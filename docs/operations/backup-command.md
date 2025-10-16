# Database Backup Command Documentation

## Overview
The `backup:database` Artisan command provides a robust solution for backing up your BilliardPro database. This command supports different database types (MySQL, PostgreSQL, SQLite) and provides flexible options for backup filenames and connections.

## Command Usage

### Basic Usage
```bash
php artisan backup:database
```

This command will create a backup with a default filename following the format: `backup_{connection}_{date}_{time}.sql`

### Options

#### --filename
Specify a custom filename for the backup:

```bash
php artisan backup:database --filename=my_backup_2023-12-01.sql
```

#### --connection
Specify which database connection to backup (useful when using multiple databases):

```bash
php artisan backup:database --connection=sqlite
```

If not specified, the command uses the default database connection configured in your `.env` file.

## Examples

### Daily Backup with Custom Name
```bash
php artisan backup:database --filename=billiardpro_daily_$(date +%Y-%m-%d).sql
```

### Backup Specific Database Connection
```bash
php artisan backup:database --connection=tenant_db --filename=tenant_backup.sql
```

## How It Works

### Backup Process
1. The command validates the specified database connection
2. Creates the `storage/backups` directory if it doesn't exist
3. Generates the appropriate backup command based on the database driver
4. Executes the backup command with proper error handling
5. Saves the backup file to `storage/backups/`

### Supported Database Drivers

#### MySQL
- Uses `mysqldump` with options for routines and triggers
- Single transaction mode to ensure consistency

#### PostgreSQL
- Uses `pg_dump` with appropriate options
- Sets the `PGPASSWORD` environment variable for authentication

#### SQLite
- Simply copies the database file since SQLite is file-based

### Backup Storage Location
All backup files are stored in:
```
storage/backups/
```

## Integration with Cron for Automated Backups

### Schedule Daily Backup
Add this line to your crontab (`sudo crontab -e`) to run a backup every day at 2 AM:

```bash
0 2 * * * cd /path/to/billiardpro && php artisan backup:database >> /var/log/billiardpro-backup.log 2>&1
```

### Weekly Full Backup with Rotation
To maintain weekly backups with file rotation:

```bash
# Create a custom script for more complex backup logic
# File: scripts/backup_with_rotation.sh
#!/bin/bash
cd /path/to/billiardpro
php artisan backup:database --filename=billiardpro_backup_$(date +%Y%m%d_%H%M%S).sql
find storage/backups -name "billiardpro_backup_*.sql" -mtime +30 -delete
```

## Security Considerations

### File Permissions
- Backup files are created with appropriate permissions (644)
- The `storage/backups` directory should not be web-accessible
- Consider encrypting backup files containing sensitive information

### Environment Security
- Database credentials are pulled from your Laravel configuration
- No credentials are passed directly through the command line
- The command follows Laravel's security practices

## Troubleshooting

### Common Issues

#### Missing Dependencies
If the command fails with "command not found" errors:
- For MySQL: Ensure `mysqldump` is installed (`sudo apt-get install mysql-client`)
- For PostgreSQL: Ensure `pg_dump` is installed (`sudo apt-get install postgresql-client`)

#### Permission Issues
If you encounter permission errors:
```bash
# Ensure the storage directory has proper permissions
sudo chown -R www-data:www-data storage/backups
sudo chmod -R 755 storage/backups
```

#### Connection Issues
If the backup fails due to connection issues:
- Verify your database connection settings in `.env`
- Check that the database service is running
- Ensure the specified database user has backup privileges

## Best Practices

1. **Regular Testing**: Periodically test restoring from your backup files
2. **Offsite Storage**: Consider copying backups to an external storage service (AWS S3, etc.)
3. **Monitoring**: Monitor backup logs to ensure backups are completing successfully
4. **Retention Policy**: Implement a strategy to remove old backup files to save space
5. **Validation**: Use checksums or file size verification to ensure backup integrity

## Integration with Deployment

### Deployment Script Example
```bash
#!/bin/bash
# deployment-backup.sh

# Create backup before deployment
php artisan backup:database --filename=pre-deployment-$(date +%Y%m%d_%H%M%S).sql

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

echo "Deployment completed with backup of previous state"
```

This command is an essential part of your BilliardPro application's backup and disaster recovery strategy, ensuring your business data remains safe and recoverable.