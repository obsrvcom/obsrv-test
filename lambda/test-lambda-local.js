/**
 * Local test script for the agent-provisioning Lambda function
 * Simulates AWS IoT events and tests API integration
 */

import fs from 'fs';
import path from 'path';

// Mock AWS IoT client for local testing
const mockIoTClient = {
    send: async (command) => {
        console.log('📡 [MOCK IoT] Publishing to topic:', command.input.topic);
        console.log('📡 [MOCK IoT] Payload:', JSON.parse(command.input.payload.toString()));
        return { MessageId: 'mock-message-id-' + Date.now() };
    }
};

// Override the IoT client in the Lambda function for local testing
process.env.API_BASE_URL = 'http://10.10.40.168:8000';  // Use your local IP
process.env.API_KEY = 'test-api-key';  // We'll skip auth for testing
process.env.IOT_ENDPOINT = 'mock-endpoint.iot.region.amazonaws.com';

async function testProvisioningEvent() {
    console.log('🧪 Testing Device Provisioning Event...\n');
    
    const provisioningEvent = {
        eventType: 'provisioning',
        deviceId: 'test-device-' + Date.now(),
        thingName: 'test-thing-' + Date.now(),
        deviceType: 'raspberry-pi-5',
        firmwareVersion: '1.0.0-test',
        ipAddress: '192.168.1.100'
    };
    
    console.log('📥 Input Event:', JSON.stringify(provisioningEvent, null, 2));
    
    try {
        const result = await handler(provisioningEvent, {});
        console.log('✅ Lambda Result:', JSON.stringify(result, null, 2));
        return result;
    } catch (error) {
        console.error('❌ Lambda Error:', error);
        return null;
    }
}

async function testPairingEvent(deviceId) {
    console.log('\n🧪 Testing Pairing Request Event...\n');
    
    const pairingEvent = {
        eventType: 'pairing',
        deviceId: deviceId,
        thingName: 'test-thing-' + Date.now(),
        topic: `device/${deviceId}/pairing/request`,
        timestamp: Date.now()
    };
    
    console.log('📥 Input Event:', JSON.stringify(pairingEvent, null, 2));
    
    try {
        const result = await handler(pairingEvent, {});
        console.log('✅ Lambda Result:', JSON.stringify(result, null, 2));
        return result;
    } catch (error) {
        console.error('❌ Lambda Error:', error);
        return null;
    }
}

async function testFleetProvisioningEvent() {
    console.log('\n🧪 Testing Fleet Provisioning Hook Event...\n');
    
    // This simulates the event AWS IoT Fleet Provisioning sends to the pre-provisioning hook
    const fleetProvisioningEvent = {
        clientId: 'fleet-device-' + Date.now(),
        certificateId: 'cert-' + Date.now(),
        certificatePem: 'mock-certificate-pem',
        templateArn: 'arn:aws:iot:region:account:provisioningtemplate/OBSRVProvisioningTemplate',
        templateName: 'OBSRVProvisioningTemplate',
        parameters: {
            DeviceType: 'raspberry-pi-5',
            FirmwareVersion: '1.0.0'
        }
    };
    
    console.log('📥 Fleet Provisioning Event:', JSON.stringify(fleetProvisioningEvent, null, 2));
    
    try {
        const result = await handler(fleetProvisioningEvent, {});
        console.log('✅ Lambda Result:', JSON.stringify(result, null, 2));
        return result;
    } catch (error) {
        console.error('❌ Lambda Error:', error);
        return null;
    }
}

async function runTests() {
    console.log('🚀 Starting Lambda Function Local Tests');
    console.log('=====================================\n');
    
    // Load the Lambda function dynamically
    const { handler } = await import('./agent-provisioning.js');
    
    // Test 1: Device Provisioning
    const provisioningResult = await testProvisioningEvent(handler);
    
    if (provisioningResult && provisioningResult.body) {
        const body = JSON.parse(provisioningResult.body);
        if (body.deviceId) {
            // Test 2: Pairing Request (using device from provisioning)
            await testPairingEvent(handler, body.deviceId);
        }
    }
    
    // Test 3: Fleet Provisioning Hook
    await testFleetProvisioningEvent(handler);
    
    console.log('\n🏁 Tests completed!');
    console.log('\nNext steps:');
    console.log('1. Check your Laravel app to see if agents were registered');
    console.log('2. Deploy the Lambda function to AWS');
    console.log('3. Configure AWS IoT Fleet Provisioning Template');
}

// Run tests
runTests().catch(console.error);