/**
 * AWS Lambda Function - Agent Provisioning & Registration
 * Handles device registration and pairing requests from AWS IoT
 */

const https = require('https');
const { IoTDataPlaneClient, PublishCommand } = require("@aws-sdk/client-iot-data-plane");

// Configuration from environment variables
const API_BASE_URL = process.env.API_BASE_URL || 'https://api.obsrv.com';
const API_KEY = process.env.API_KEY;
const IOT_ENDPOINT = process.env.IOT_ENDPOINT;

// Initialize IoT client
const iotClient = new IoTDataPlaneClient({ 
    region: process.env.AWS_REGION || 'eu-west-2',
    endpoint: `https://${IOT_ENDPOINT}`
});

/**
 * Main Lambda handler
 */
exports.handler = async (event, context) => {
    console.log('Received event:', JSON.stringify(event, null, 2));
    
    try {
        // Handle different event types
        let result;
        
        if (event.eventType === 'provisioning' || event.eventType === 'register') {
            // Device registration/provisioning
            result = await handleDeviceRegistration(event);
        } else if (event.eventType === 'pairing' || event.topic?.includes('/pairing/')) {
            // Pairing request
            result = await handlePairingRequest(event);
        } else {
            // Default: try to detect from topic or data
            if (event.topic) {
                const topicParts = event.topic.split('/');
                if (topicParts.includes('pairing')) {
                    result = await handlePairingRequest(event);
                } else {
                    result = await handleDeviceRegistration(event);
                }
            } else {
                result = await handleDeviceRegistration(event);
            }
        }
        
        return {
            statusCode: 200,
            body: JSON.stringify(result),
        };
        
    } catch (error) {
        console.error('Error processing event:', error);
        
        return {
            statusCode: 500,
            body: JSON.stringify({
                success: false,
                error: error.message,
            }),
        };
    }
};

/**
 * Handle device registration with our API
 */
async function handleDeviceRegistration(event) {
    console.log('Processing device registration...');
    
    const deviceId = event.deviceId || event.clientId;
    const thingName = event.thingName || event.certificateId;
    
    if (!deviceId) {
        throw new Error('Device ID is required for registration');
    }
    
    // Register device with our API
    const registrationData = {
        device_id: deviceId,
        thing_name: thingName,
        type: event.deviceType || 'raspberry-pi-5',
        firmware_version: event.firmwareVersion,
        ip_address: event.ipAddress,
    };
    
    console.log('Registering device with API:', registrationData);
    
    const response = await makeApiRequest('/api/v1/agents/register', 'POST', registrationData);
    
    if (response.success) {
        console.log(`Device ${deviceId} registered successfully`);
        
        // Publish registration confirmation back to device
        const confirmationPayload = {
            success: true,
            deviceId: deviceId,
            thingName: thingName,
            agentId: response.agent_id,
            pairingCode: response.pairing_code,
            expiresAt: response.expires_at,
            message: 'Device registered successfully. Use the pairing code to assign to a site.'
        };
        
        await publishToDevice(deviceId, 'register/response', confirmationPayload);
        
        return confirmationPayload;
    } else {
        throw new Error(`Failed to register device: ${response.error || 'Unknown error'}`);
    }
}

/**
 * Handle pairing code request
 */
async function handlePairingRequest(event) {
    console.log('Processing pairing request...');
    
    const deviceId = event.deviceId || extractDeviceIdFromTopic(event.topic);
    const thingName = event.thingName;
    
    if (!deviceId) {
        throw new Error('Device ID is required for pairing');
    }
    
    // Request new pairing code from our API
    const pairingData = {
        device_id: deviceId,
        thing_name: thingName,
    };
    
    console.log('Requesting pairing code from API:', pairingData);
    
    const response = await makeApiRequest('/api/v1/agents/pairing-code', 'POST', pairingData);
    
    if (response.success) {
        console.log(`Pairing code generated for device ${deviceId}: ${response.pairing_code}`);
        
        // Publish pairing code back to device
        const pairingPayload = {
            success: true,
            deviceId: deviceId,
            thingName: thingName,
            pairingCode: response.pairing_code,
            expiresAt: response.expires_at,
        };
        
        await publishToDevice(deviceId, 'pairing/response', pairingPayload);
        
        return pairingPayload;
    } else {
        throw new Error(`Failed to generate pairing code: ${response.error || 'Unknown error'}`);
    }
}

/**
 * Make HTTP request to the Laravel API
 */
function makeApiRequest(endpoint, method, data) {
    return new Promise((resolve, reject) => {
        const url = new URL(API_BASE_URL + endpoint);
        
        const options = {
            hostname: url.hostname,
            port: url.port || (url.protocol === 'https:' ? 443 : 80),
            path: url.pathname + url.search,
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'User-Agent': 'OBSRV-Lambda/1.0',
            },
        };
        
        // Add API key if configured
        if (API_KEY) {
            options.headers['X-API-Key'] = API_KEY;
            options.headers['Authorization'] = `Bearer ${API_KEY}`;
        }
        
        if (data && method !== 'GET') {
            const postData = JSON.stringify(data);
            options.headers['Content-Length'] = Buffer.byteLength(postData);
        }
        
        console.log(`Making ${method} request to ${url.toString()}`);
        
        const protocol = url.protocol === 'https:' ? https : require('http');
        const req = protocol.request(options, (res) => {
            let responseBody = '';
            
            res.on('data', (chunk) => {
                responseBody += chunk;
            });
            
            res.on('end', () => {
                console.log(`API Response Status: ${res.statusCode}`);
                console.log(`API Response Body: ${responseBody}`);
                
                try {
                    const response = JSON.parse(responseBody);
                    
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve(response);
                    } else {
                        reject(new Error(`API request failed with status ${res.statusCode}: ${responseBody}`));
                    }
                } catch (error) {
                    reject(new Error(`Failed to parse API response: ${responseBody}`));
                }
            });
        });
        
        req.on('error', (error) => {
            reject(new Error(`Network error: ${error.message}`));
        });
        
        req.setTimeout(30000, () => {
            req.destroy();
            reject(new Error('Request timeout'));
        });
        
        // Write data if POST/PUT
        if (data && method !== 'GET') {
            req.write(JSON.stringify(data));
        }
        
        req.end();
    });
}

/**
 * Publish message back to device via IoT
 */
async function publishToDevice(deviceId, messageType, payload) {
    const topic = `device/${deviceId}/${messageType}`;
    
    const command = new PublishCommand({
        topic: topic,
        payload: Buffer.from(JSON.stringify(payload)),
        qos: 1
    });
    
    console.log(`Publishing to IoT topic: ${topic}`);
    console.log(`Payload: ${JSON.stringify(payload)}`);
    
    try {
        const response = await iotClient.send(command);
        console.log(`Successfully published to device ${deviceId}`, response);
    } catch (error) {
        console.error(`Failed to publish to device ${deviceId}:`, error);
        throw error;
    }
}

/**
 * Extract device ID from IoT topic
 */
function extractDeviceIdFromTopic(topic) {
    if (!topic) return null;
    
    // Expected format: device/{deviceId}/something
    const parts = topic.split('/');
    if (parts.length >= 3 && parts[0] === 'device') {
        return parts[1];
    }
    
    return null;
}