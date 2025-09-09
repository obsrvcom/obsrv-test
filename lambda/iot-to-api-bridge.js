/**
 * AWS Lambda Function - IoT to API Bridge
 * Forwards messages from AWS IoT to the Laravel API
 */

const https = require('https');

// Configuration from environment variables
const API_BASE_URL = process.env.API_BASE_URL || 'https://api.obsrv.io';
const API_KEY = process.env.API_KEY;

/**
 * Main Lambda handler
 */
exports.handler = async (event) => {
    console.log('Received IoT event:', JSON.stringify(event, null, 2));
    
    try {
        // Parse the topic to determine the message type
        const topic = event.topic || '';
        const topicParts = topic.split('/');
        
        // Expected topic formats:
        // device/{deviceId}/pairing/request
        // device/{deviceId}/heartbeat
        // device/{deviceId}/telegrams
        // device/{deviceId}/status
        
        if (topicParts.length < 3) {
            throw new Error(`Invalid topic format: ${topic}`);
        }
        
        const deviceId = topicParts[1];
        const messageType = topicParts[2];
        
        // Route based on message type
        let endpoint;
        let method = 'POST';
        let payload = event;
        
        switch (messageType) {
            case 'pairing':
                if (topicParts[3] === 'request') {
                    endpoint = '/api/v1/agents/pairing-code';
                    payload = {
                        device_id: deviceId,
                        thing_name: event.thingName,
                    };
                }
                break;
                
            case 'heartbeat':
                endpoint = '/api/v1/agents/heartbeat';
                payload = {
                    device_id: deviceId,
                    status: event.status || 'healthy',
                    metrics: event.metrics || {},
                    knx_status: event.knx_status || {},
                    uptime: event.uptime,
                    ip_address: event.ip_address,
                };
                break;
                
            case 'telegrams':
                endpoint = '/api/v1/agents/telegrams';
                payload = {
                    device_id: deviceId,
                    telegrams: event.telegrams || [],
                };
                break;
                
            case 'register':
                endpoint = '/api/v1/agents/register';
                payload = {
                    device_id: deviceId,
                    thing_name: event.thingName,
                    type: event.type || 'raspberry-pi-5',
                    firmware_version: event.firmware_version,
                    ip_address: event.ip_address,
                };
                break;
                
            case 'status':
                endpoint = `/api/v1/agents/${deviceId}/status`;
                method = 'GET';
                payload = null;
                break;
                
            default:
                throw new Error(`Unknown message type: ${messageType}`);
        }
        
        if (!endpoint) {
            throw new Error(`No endpoint configured for message type: ${messageType}`);
        }
        
        // Make API request
        const response = await makeApiRequest(endpoint, method, payload);
        
        console.log(`Successfully processed ${messageType} for device ${deviceId}`);
        console.log('API Response:', response);
        
        // If this was a pairing request and we got a pairing code, publish it back to the device
        if (messageType === 'pairing' && response.pairing_code) {
            const iotResponse = {
                success: true,
                deviceId: deviceId,
                thingName: event.thingName,
                pairingCode: response.pairing_code,
                expiresAt: response.expires_at,
            };
            
            // Note: To publish back to IoT, you'd need to use AWS SDK
            // For now, we'll just return the response
            return iotResponse;
        }
        
        return {
            statusCode: 200,
            body: JSON.stringify({
                success: true,
                message: `Processed ${messageType} for device ${deviceId}`,
                response: response,
            }),
        };
        
    } catch (error) {
        console.error('Error processing IoT message:', error);
        
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
 * Make HTTP request to the API
 */
function makeApiRequest(endpoint, method, data) {
    return new Promise((resolve, reject) => {
        const url = new URL(API_BASE_URL + endpoint);
        
        const options = {
            hostname: url.hostname,
            port: url.port || 443,
            path: url.pathname + url.search,
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-API-Key': API_KEY,
            },
        };
        
        if (data && method !== 'GET') {
            const postData = JSON.stringify(data);
            options.headers['Content-Length'] = Buffer.byteLength(postData);
        }
        
        const req = https.request(options, (res) => {
            let responseBody = '';
            
            res.on('data', (chunk) => {
                responseBody += chunk;
            });
            
            res.on('end', () => {
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
        
        // Write data if POST/PUT
        if (data && method !== 'GET') {
            req.write(JSON.stringify(data));
        }
        
        req.end();
    });
}