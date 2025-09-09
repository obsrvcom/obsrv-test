# Lambda Functions for OBSRV Agent System

This directory contains AWS Lambda functions for the OBSRV agent system.

## agent-provisioning.js

This Lambda function handles device registration and pairing requests from AWS IoT fleet provisioning. It integrates directly with our Laravel API to register agents and generate pairing codes.

**Use Case**: Called during AWS IoT fleet provisioning to register new devices and generate initial pairing codes.

## iot-to-api-bridge.js

This Lambda function acts as a bridge between AWS IoT Core and the Laravel API, forwarding messages from IoT devices to the appropriate API endpoints.

**Use Case**: Forwards ongoing IoT messages (heartbeats, telegrams, status updates) to the Laravel API.

## Deployment

### agent-provisioning.js

1. **Create deployment package:**
```bash
zip lambda-agent-provisioning.zip agent-provisioning.js
```

2. **Create Lambda function:**
```bash
aws lambda create-function \
  --function-name obsrv-agent-provisioning \
  --runtime nodejs18.x \
  --role arn:aws:iam::YOUR_ACCOUNT:role/lambda-iot-role \
  --handler agent-provisioning.handler \
  --zip-file fileb://lambda-agent-provisioning.zip \
  --timeout 30 \
  --environment "Variables={API_BASE_URL=https://api.obsrv.com,API_KEY=your-secret-api-key,IOT_ENDPOINT=your-iot-endpoint.iot.region.amazonaws.com}"
```

3. **Update function (if already exists):**
```bash
aws lambda update-function-code \
  --function-name obsrv-agent-provisioning \
  --zip-file fileb://lambda-agent-provisioning.zip
```

4. **Configure as pre-provisioning hook in AWS IoT:**
   - Go to AWS IoT Core > Manage > Fleet provisioning templates
   - Add `obsrv-agent-provisioning` as the pre-provisioning hook
   - The function will be called during device provisioning

### iot-to-api-bridge.js

### Deployment

1. **Create deployment package:**
```bash
zip lambda-iot-bridge.zip iot-to-api-bridge.js
```

2. **Create Lambda function:**
```bash
aws lambda create-function \
  --function-name obsrv-iot-to-api-bridge \
  --runtime nodejs18.x \
  --role arn:aws:iam::YOUR_ACCOUNT:role/lambda-iot-role \
  --handler iot-to-api-bridge.handler \
  --zip-file fileb://lambda-iot-bridge.zip \
  --timeout 30 \
  --environment "Variables={API_BASE_URL=https://api.obsrv.io,API_KEY=your-secret-api-key}"
```

3. **Update function (if already exists):**
```bash
aws lambda update-function-code \
  --function-name obsrv-iot-to-api-bridge \
  --zip-file fileb://lambda-iot-bridge.zip
```

### Configuration

Environment variables:
- `API_BASE_URL`: Your Laravel API base URL (e.g., `https://api.obsrv.io`)
- `API_KEY`: Secret API key for authenticating with the Laravel API

### IAM Role Requirements

The Lambda execution role needs:
1. Basic Lambda execution permissions
2. (Optional) IoT publish permissions if you want to send responses back to devices

### IoT Rule Configuration

Create an IoT rule to trigger this Lambda:

```sql
SELECT *, topic() as topic 
FROM 'device/+/+' 
WHERE topic(2) IN ('register', 'pairing', 'heartbeat', 'telegrams', 'status')
```

Action: Invoke Lambda function `obsrv-iot-to-api-bridge`

### Supported Topics

The Lambda function handles the following IoT topics:

- `device/{deviceId}/register` - Register a new agent
- `device/{deviceId}/pairing/request` - Request a pairing code
- `device/{deviceId}/heartbeat` - Send heartbeat data
- `device/{deviceId}/telegrams` - Send KNX telegrams
- `device/{deviceId}/status` - Get agent status

### Message Formats

#### Register
```json
{
  "thingName": "thing-123",
  "type": "raspberry-pi-5",
  "firmware_version": "1.0.0",
  "ip_address": "192.168.1.100"
}
```

#### Heartbeat
```json
{
  "status": "healthy",
  "metrics": {
    "cpu": 45,
    "memory": 60,
    "disk": 30,
    "temperature": 55
  },
  "knx_status": {
    "monitors": 2,
    "active": 2
  },
  "uptime": 86400,
  "ip_address": "192.168.1.100"
}
```

#### Telegrams
```json
{
  "telegrams": [
    {
      "source": "1.1.1",
      "destination": "2/3/4",
      "service": "GroupValueWrite",
      "data": "01",
      "timestamp": "2024-01-09T12:00:00Z"
    }
  ]
}
```

## Testing

You can test the Lambda function using the AWS CLI:

```bash
aws lambda invoke \
  --function-name obsrv-iot-to-api-bridge \
  --payload '{"topic":"device/test-device/heartbeat","status":"healthy"}' \
  response.json
```

## Monitoring

View Lambda logs in CloudWatch:
```bash
aws logs tail /aws/lambda/obsrv-iot-to-api-bridge --follow
```