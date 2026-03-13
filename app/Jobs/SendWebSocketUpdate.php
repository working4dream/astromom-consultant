<?php
namespace App\Jobs;

use Aws\ApiGatewayManagementApi\ApiGatewayManagementApiClient;
use Aws\Exception\AwsException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Log;

class SendWebSocketUpdate
{
    protected $userId;
    protected $message;
    private array $extra;

    public function __construct($userId, mixed $message, array $extra = [])
    {
        $this->userId = (string) $userId;
        $this->message = $message;
        $this->extra = $extra;
    }

    public function handle()
    {
        $region = 'ap-south-1';
        $apiUrl = 'https://blphbnlpof.execute-api.ap-south-1.amazonaws.com/production';

        // 1️⃣ DynamoDB Client
        $dynamoDb = new DynamoDbClient([
            'region' => $region,
            'version' => 'latest',
        ]);

        // 2️⃣ Get All WebSocket Connections for Seller
        $result = $dynamoDb->query([
            'TableName' => 'LiveChatConnections',
            'IndexName' => 'userId-index', // GSI for userId
            'KeyConditionExpression' => 'userId = :userId',
            'ExpressionAttributeValues' => [
                ':userId' => ['S' => $this->userId]
            ]
        ]);

        // 3️⃣ API Gateway WebSocket Client
        $client = new ApiGatewayManagementApiClient([
            'region'  => $region,
            'version' => 'latest',
            'endpoint' => $apiUrl, // Must match WebSocket API
        ]);

        foreach ($result['Items'] as $item) {
            $connectionId = $item['connectionId']['S'];

            try {
                // 4️⃣ Send message to WebSocket connection
                $client->postToConnection([
                    'actionName' => 'sendUpdate',
                    'ConnectionId' => $connectionId,
                    'Data' => json_encode([
                        'message' => $this->message,
                        'extra' => $this->extra,
                        'userId' => $this->userId
                    ])
                ]);
            } catch (AwsException $e) {
                // Log::error("Failed to send message to $connectionId: " . $e->getMessage());
            }
        }
    }
}