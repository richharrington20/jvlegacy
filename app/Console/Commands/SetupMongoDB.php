<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Client;

class SetupMongoDB extends Command
{
    protected $signature = 'mongodb:setup';
    protected $description = 'Set up MongoDB database structure with collections and indexes';

    public function handle()
    {
        $this->info('🚀 Setting up MongoDB database structure...');
        $this->newLine();

        try {
            $connection = DB::connection('legacy');
            
            // Get MongoDB client
            $client = $this->getMongoClient($connection);
            $database = $client->selectDatabase(config('database.connections.legacy.database'));

            // List of collections to create with their indexes
            $collections = $this->getCollectionsConfig();

            foreach ($collections as $collectionName => $config) {
                $this->info("📦 Setting up collection: {$collectionName}");

                try {
                    // Create collection (MongoDB creates it automatically on first insert, but we'll ensure it exists)
                    $collection = $database->selectCollection($collectionName);
                    
                    // Drop existing indexes (except _id) to recreate them
                    $existingIndexes = $collection->listIndexes();
                    foreach ($existingIndexes as $index) {
                        $indexName = $index->getName();
                        if ($indexName !== '_id_') {
                            try {
                                $collection->dropIndex($indexName);
                            } catch (\Exception $e) {
                                // Index might not exist, continue
                            }
                        }
                    }

                    // Create indexes
                    if (!empty($config['indexes'])) {
                        foreach ($config['indexes'] as $indexName => $indexDef) {
                            try {
                                $options = ['name' => $indexName];
                                if (isset($indexDef['unique']) && $indexDef['unique']) {
                                    $options['unique'] = true;
                                }
                                if (isset($indexDef['sparse']) && $indexDef['sparse']) {
                                    $options['sparse'] = true;
                                }

                                $collection->createIndex($indexDef['keys'], $options);
                                $this->line("  ✅ Created index: {$indexName}");
                            } catch (\Exception $e) {
                                $this->warn("  ⚠️  Failed to create index {$indexName}: " . $e->getMessage());
                            }
                        }
                    }

                    $this->info("  ✅ Collection '{$collectionName}' ready");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to setup collection '{$collectionName}': " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info('✅ MongoDB database structure setup complete!');
            $this->newLine();
            $this->info('📊 Collections created:');
            foreach (array_keys($collections) as $collectionName) {
                $this->line("  - {$collectionName}");
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to setup MongoDB: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    protected function getMongoClient($connection)
    {
        // Try to get client from connection
        try {
            if (method_exists($connection, 'getMongoClient')) {
                return $connection->getMongoClient();
            }
        } catch (\Exception $e) {
            // Continue to fallback
        }

        // Fallback: create client directly from config
        $dsn = config('database.connections.legacy.dsn');
        
        if (!$dsn) {
            // Build DSN from components
            $host = config('database.connections.legacy.host');
            $port = config('database.connections.legacy.port', 27017);
            $username = config('database.connections.legacy.username');
            $password = config('database.connections.legacy.password');
            
            if ($username && $password) {
                $dsn = "mongodb://{$username}:{$password}@{$host}:{$port}";
            } else {
                $dsn = "mongodb://{$host}:{$port}";
            }
        }

        $options = config('database.connections.legacy.options', []);
        
        return new Client($dsn, $options);
    }

    protected function getCollectionsConfig(): array
    {
        return [
            'accounts' => [
                'indexes' => [
                    'email_unique' => [
                        'keys' => ['email' => 1],
                        'unique' => true,
                        'sparse' => true,
                    ],
                    'person_id' => [
                        'keys' => ['person_id' => 1],
                    ],
                    'company_id' => [
                        'keys' => ['company_id' => 1],
                    ],
                    'type_id' => [
                        'keys' => ['type_id' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                ],
            ],
            'people' => [
                'indexes' => [
                    'email' => [
                        'keys' => ['email' => 1],
                    ],
                    'active' => [
                        'keys' => ['active' => 1],
                    ],
                ],
            ],
            'companies' => [
                'indexes' => [
                    'email' => [
                        'keys' => ['email' => 1],
                    ],
                    'number' => [
                        'keys' => ['number' => 1],
                    ],
                ],
            ],
            'projects' => [
                'indexes' => [
                    'project_id_unique' => [
                        'keys' => ['project_id' => 1],
                        'unique' => true,
                        'sparse' => true,
                    ],
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'status' => [
                        'keys' => ['status' => 1],
                    ],
                    'show_to_investors' => [
                        'keys' => ['show_to_investors' => 1],
                    ],
                ],
            ],
            'project_log' => [
                'indexes' => [
                    'project_id' => [
                        'keys' => ['project_id' => 1],
                    ],
                    'category' => [
                        'keys' => ['category' => 1],
                    ],
                    'sent' => [
                        'keys' => ['sent' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                    'sent_on' => [
                        'keys' => ['sent_on' => -1], // Descending for recent first
                    ],
                    'project_sent' => [
                        'keys' => [
                            'project_id' => 1,
                            'sent' => 1,
                        ],
                    ],
                ],
            ],
            'project_investments' => [
                'indexes' => [
                    'project_id' => [
                        'keys' => ['project_id' => 1],
                    ],
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'paid' => [
                        'keys' => ['paid' => 1],
                    ],
                    'project_paid' => [
                        'keys' => [
                            'project_id' => 1,
                            'paid' => 1,
                        ],
                    ],
                ],
            ],
            'project_documents' => [
                'indexes' => [
                    'project_id' => [
                        'keys' => ['project_id' => 1],
                    ],
                    'category' => [
                        'keys' => ['category' => 1],
                    ],
                    'show_to_investors' => [
                        'keys' => ['show_to_investors' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                ],
            ],
            'account_documents' => [
                'indexes' => [
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'category' => [
                        'keys' => ['category' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                ],
            ],
            'update_images' => [
                'indexes' => [
                    'update_id' => [
                        'keys' => ['update_id' => 1],
                    ],
                    'display_order' => [
                        'keys' => ['display_order' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                ],
            ],
            'email_logs' => [
                'indexes' => [
                    'message_id_unique' => [
                        'keys' => ['message_id' => 1],
                        'unique' => true,
                        'sparse' => true,
                    ],
                    'email_type' => [
                        'keys' => ['email_type' => 1],
                    ],
                    'recipient_email' => [
                        'keys' => ['recipient_email' => 1],
                    ],
                    'recipient_account_id' => [
                        'keys' => ['recipient_account_id' => 1],
                    ],
                    'status' => [
                        'keys' => ['status' => 1],
                    ],
                    'sent_at' => [
                        'keys' => ['sent_at' => -1], // Descending
                    ],
                    'project_id' => [
                        'keys' => ['project_id' => 1],
                    ],
                    'update_id' => [
                        'keys' => ['update_id' => 1],
                    ],
                    'status_sent_at' => [
                        'keys' => [
                            'status' => 1,
                            'sent_at' => -1,
                        ],
                    ],
                    'email_type_sent_at' => [
                        'keys' => [
                            'email_type' => 1,
                            'sent_at' => -1,
                        ],
                    ],
                ],
            ],
            'account_types' => [
                'indexes' => [
                    'name' => [
                        'keys' => ['name' => 1],
                    ],
                ],
            ],
            'account_shares' => [
                'indexes' => [
                    'primary_account_id' => [
                        'keys' => ['primary_account_id' => 1],
                    ],
                    'shared_account_id' => [
                        'keys' => ['shared_account_id' => 1],
                    ],
                    'status' => [
                        'keys' => ['status' => 1],
                    ],
                    'deleted' => [
                        'keys' => ['deleted' => 1],
                    ],
                ],
            ],
            'properties' => [
                'indexes' => [
                    'proposal_id' => [
                        'keys' => ['proposal_id' => 1],
                    ],
                ],
            ],
            'support_tickets' => [
                'indexes' => [
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'status' => [
                        'keys' => ['status' => 1],
                    ],
                    'created_at' => [
                        'keys' => ['created_at' => -1],
                    ],
                ],
            ],
            'investor_notifications' => [
                'indexes' => [
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'read' => [
                        'keys' => ['read' => 1],
                    ],
                    'created_at' => [
                        'keys' => ['created_at' => -1],
                    ],
                ],
            ],
            'document_email_logs' => [
                'indexes' => [
                    'account_id' => [
                        'keys' => ['account_id' => 1],
                    ],
                    'project_id' => [
                        'keys' => ['project_id' => 1],
                    ],
                    'sent_at' => [
                        'keys' => ['sent_at' => -1],
                    ],
                ],
            ],
        ];
    }
}

