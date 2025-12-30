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

        // Collect MongoDB configuration values
        $config = [
            'dsn' => config('database.connections.legacy.dsn'),
            'host' => config('database.connections.legacy.host'),
            'port' => config('database.connections.legacy.port', 27017),
            'username' => config('database.connections.legacy.username'),
            'password' => config('database.connections.legacy.password'),
            'database' => config('database.connections.legacy.database', 'admin'),
            'auth_database' => config('database.connections.legacy.options.database', 'admin'),
        ];
        
        $dsn = $config['dsn'];
        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Check if host is already a connection string (mongodb:// or mongodb+srv://)
        $hostIsConnectionString = !empty($host) && (str_starts_with($host, 'mongodb://') || str_starts_with($host, 'mongodb+srv://'));
        
        // Determine if we have enough information to connect
        $hasDsn = !empty($dsn);
        $hasHost = !empty($host);
        $hasCredentials = !empty($username) && !empty($password);
        $hasEnoughInfo = $hasDsn || ($hasHost && $hasCredentials);
        
        if (!$hasEnoughInfo) {
            $this->error('❌ MongoDB configuration is incomplete!');
            $this->newLine();
            
            // Display current config values
            $this->displayConfigValues($config);
            
            // Provide specific guidance based on what's missing
            $this->error('Missing required configuration:');
            $this->newLine();
            
            if (!$hasDsn && !$hasHost) {
                $this->line('  ❌ Either DB_LEGACY_DSN or DB_LEGACY_HOST must be set');
            } elseif ($hasHost && !$hasCredentials) {
                $this->line('  ❌ DB_LEGACY_USERNAME and DB_LEGACY_PASSWORD are required when using DB_LEGACY_HOST');
            }
            
            $this->newLine();
            $this->line('📝 Configuration options:');
            $this->newLine();
            
            $this->line('Option 1: Use full connection string (DSN)');
            $this->line('  DB_LEGACY_DSN=mongodb+srv://username:password@host/database?authSource=admin');
            $this->newLine();
            
            $this->line('Option 2: Use separate variables');
            if (!$hasHost) {
                $this->line('  DB_LEGACY_HOST=your-mongodb-host (or mongodb+srv://hostname for SRV)');
            }
            if (!$hasCredentials) {
                $this->line('  DB_LEGACY_USERNAME=your-username');
                $this->line('  DB_LEGACY_PASSWORD=your-password');
            }
            $this->line('  DB_LEGACY_DATABASE=your-database (optional, defaults to admin)');
            $this->line('  DB_LEGACY_PORT=27017 (optional, defaults to 27017)');
            $this->newLine();
            
            $this->line('💡 For DigitalOcean MongoDB:');
            $this->line('  - Use mongodb+srv:// protocol for SRV connections');
            $this->line('  - Set DB_LEGACY_AUTHENTICATION_DATABASE=admin if different from database');
            $this->newLine();
            
            $this->warn('After updating .env, run: php artisan config:clear');
            
            return 1;
        }

        try {
            // Get MongoDB client directly (bypass Laravel connection if config is incomplete)
            $client = $this->getMongoClient(null);
            $databaseName = config('database.connections.legacy.database', 'admin');
            $database = $client->selectDatabase($databaseName);

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

    /**
     * Safely display config values for debugging (masking sensitive data)
     */
    protected function displayConfigValues(array $config): void
    {
        $this->line('📋 Current MongoDB configuration:');
        $this->newLine();
        
        $displayConfig = [
            'DSN' => $config['dsn'] ?? '❌ Not set',
            'Host' => $config['host'] ?? '❌ Not set',
            'Port' => $config['port'] ?? '27017 (default)',
            'Username' => $config['username'] ?? '❌ Not set',
            'Password' => !empty($config['password']) ? '***' . substr($config['password'], -2) : '❌ Not set',
            'Database' => $config['database'] ?? 'admin (default)',
            'Auth Database' => $config['auth_database'] ?? 'admin (default)',
        ];
        
        foreach ($displayConfig as $key => $value) {
            $status = str_contains($value, '❌') ? '  ❌' : '  ✓';
            $this->line("{$status} {$key}: {$value}");
        }
        
        $this->newLine();
    }

    protected function getMongoClient($connection = null)
    {
        // Try to get client from connection if provided
        if ($connection !== null) {
            try {
                if (method_exists($connection, 'getMongoClient')) {
                    return $connection->getMongoClient();
                }
            } catch (\Exception $e) {
                // Continue to fallback
            }
        }

        // Create client directly from config
        $dsn = config('database.connections.legacy.dsn');
        
        if (empty($dsn)) {
            // Build DSN from components
            $host = config('database.connections.legacy.host');
            $port = config('database.connections.legacy.port', 27017);
            $username = config('database.connections.legacy.username');
            $password = config('database.connections.legacy.password');
            $database = config('database.connections.legacy.database', 'admin');
            $authDatabase = config('database.connections.legacy.options.database', 'admin');
            
            if (empty($host)) {
                throw new \Exception('MongoDB host is required. Set DB_LEGACY_HOST in your .env file.');
            }
            
            // Check if host is already a connection string (mongodb:// or mongodb+srv://)
            $isSrv = str_starts_with($host, 'mongodb+srv://');
            $isStandard = str_starts_with($host, 'mongodb://');
            
            if ($isSrv || $isStandard) {
                // Extract hostname from connection string
                $hostname = preg_replace('/^mongodb\+?srv?:\/\//', '', $host);
                $hostname = preg_replace('/\/.*$/', '', $hostname); // Remove path if present
                $hostname = preg_replace('/\?.*$/', '', $hostname); // Remove query string if present
                $hostname = preg_replace('/@.*$/', '', $hostname); // Remove credentials if present
                
                // Use SRV protocol if original was SRV
                $protocol = $isSrv ? 'mongodb+srv' : 'mongodb';
                
                // Build connection string with credentials
                if ($username && $password) {
                    $dsn = "{$protocol}://{$username}:{$password}@{$hostname}/{$database}";
                } else {
                    $dsn = "{$protocol}://{$hostname}/{$database}";
                }
                
                // Add authSource if different from database
                if ($authDatabase !== $database) {
                    $dsn .= "?authSource={$authDatabase}";
                }
            } else {
                // Standard hostname, build connection string
                if ($username && $password) {
                    $dsn = "mongodb://{$username}:{$password}@{$host}:{$port}/{$database}";
                } else {
                    $dsn = "mongodb://{$host}:{$port}/{$database}";
                }
                
                // Add authSource if different from database
                if ($authDatabase !== $database) {
                    $dsn .= "?authSource={$authDatabase}";
                }
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

