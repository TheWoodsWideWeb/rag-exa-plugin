# Deployment Guide

## AI Trainer Dashboard Plugin Deployment

This guide provides comprehensive instructions for deploying the AI Trainer Dashboard plugin in various environments, from local development to production servers.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Local Development Setup](#local-development-setup)
- [Staging Environment](#staging-environment)
- [Production Deployment](#production-deployment)
- [WordPress.org Distribution](#wordpressorg-distribution)
- [Docker Deployment](#docker-deployment)
- [Cloud Deployment](#cloud-deployment)
- [Performance Optimization](#performance-optimization)
- [Monitoring and Logging](#monitoring-and-logging)
- [Backup and Recovery](#backup-and-recovery)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.0 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Memory**: Minimum 256MB RAM (512MB recommended)
- **Storage**: 100MB free space minimum
- **Extensions**: cURL, JSON, mbstring, fileinfo

### Required Software

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Extensions**: 
  - cURL (for API communication)
  - JSON (for data processing)
  - mbstring (for text processing)
  - fileinfo (for file uploads)
  - zip (for file extraction)
  - gd (for image processing)
- **Node.js**: 14+ (for build process)
- **Composer**: Latest version (for PHP dependencies)

### API Keys Required

- **OpenAI API Key**: For embeddings and AI processing
- **Exa.ai API Key**: For neural search functionality

## Local Development Setup

### 1. Clone Repository

```bash
# Clone the repository
git clone https://github.com/samnguyen92/rag-exa-plugin.git
cd rag-exa-plugin

# Checkout the latest stable version
git checkout v1.1.0
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### 3. Environment Configuration

Create a `.env` file in the root directory:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_EMBEDDING_MODEL=text-embedding-ada-002

# Exa.ai Configuration
EXA_API_KEY=your_exa_api_key_here
EXA_BASE_URL=https://api.exa.ai

# WordPress Configuration
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=false

# Database Configuration
DB_HOST=localhost
DB_NAME=wordpress
DB_USER=wp_user
DB_PASSWORD=wp_password

# Plugin Configuration
AI_TRAINER_VERSION=1.1.0
AI_TRAINER_ENVIRONMENT=development
```

### 4. WordPress Integration

```bash
# Copy plugin to WordPress plugins directory
cp -r rag-exa-plugin /path/to/wordpress/wp-content/plugins/ai-trainer

# Set proper permissions
chmod -R 755 /path/to/wordpress/wp-content/plugins/ai-trainer
chown -R www-data:www-data /path/to/wordpress/wp-content/plugins/ai-trainer
```

### 5. Database Setup

```sql
-- Create plugin database tables (automatically handled by plugin)
-- The plugin will create tables on activation

-- Verify tables exist
SHOW TABLES LIKE 'wp_ai_trainer_%';
```

### 6. Plugin Activation

1. **WordPress Admin**: Go to Plugins > Installed Plugins
2. **Activate**: Click "Activate" for "AI Trainer Dashboard"
3. **Configure**: Go to AI Trainer > Settings
4. **API Keys**: Enter your OpenAI and Exa.ai API keys
5. **Test**: Verify functionality in the admin interface

## Staging Environment

### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required software
sudo apt install -y apache2 php mysql-server composer nodejs npm

# Install PHP extensions
sudo apt install -y php-curl php-json php-mbstring php-fileinfo php-zip php-gd
```

### 2. WordPress Installation

```bash
# Download WordPress
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
sudo mv wordpress /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/wordpress
sudo chmod -R 755 /var/www/html/wordpress
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE wordpress_staging;
CREATE USER 'wp_staging'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON wordpress_staging.* TO 'wp_staging'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Plugin Deployment

```bash
# Deploy plugin
cd /var/www/html/wordpress/wp-content/plugins/
sudo git clone https://github.com/samnguyen92/rag-exa-plugin.git ai-trainer

# Install dependencies
cd ai-trainer
sudo composer install --no-dev --optimize-autoloader
sudo npm install
sudo npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/html/wordpress/wp-content/plugins/ai-trainer
sudo chmod -R 755 /var/www/html/wordpress/wp-content/plugins/ai-trainer
```

### 5. Configuration

Create staging-specific configuration:

```php
// wp-config.php additions
define('WP_ENVIRONMENT_TYPE', 'staging');
define('AI_TRAINER_ENVIRONMENT', 'staging');
define('AI_TRAINER_DEBUG', true);
```

## Production Deployment

### 1. Server Requirements

- **CPU**: 2+ cores recommended
- **RAM**: 4GB+ recommended
- **Storage**: SSD with 20GB+ free space
- **Bandwidth**: 100Mbps+ recommended
- **SSL Certificate**: Required for HTTPS

### 2. Security Hardening

```bash
# Secure file permissions
sudo find /var/www/html/wordpress -type d -exec chmod 755 {} \;
sudo find /var/www/html/wordpress -type f -exec chmod 644 {} \;
sudo chmod 600 /var/www/html/wordpress/wp-config.php

# Configure firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Install SSL certificate
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### 3. Performance Optimization

```apache
# Apache configuration (/etc/apache2/sites-available/wordpress.conf)
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/wordpress
    
    <Directory /var/www/html/wordpress>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Enable compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
    </IfModule>
    
    # Enable caching
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/ico "access plus 1 month"
        ExpiresByType image/icon "access plus 1 month"
        ExpiresByType text/plain "access plus 1 month"
        ExpiresByType image/x-icon "access plus 1 month"
        ExpiresByType application/pdf "access plus 1 month"
        ExpiresByType application/x-shockwave-flash "access plus 1 month"
        ExpiresByType image/svg+xml "access plus 1 month"
    </IfModule>
</VirtualHost>
```

### 4. Database Optimization

```sql
-- Optimize database tables
OPTIMIZE TABLE wp_ai_trainer_qa;
OPTIMIZE TABLE wp_ai_trainer_text;
OPTIMIZE TABLE wp_ai_trainer_files;
OPTIMIZE TABLE wp_ai_trainer_interactions;

-- Create indexes for better performance
CREATE INDEX idx_qa_created_at ON wp_ai_trainer_qa(created_at);
CREATE INDEX idx_text_created_at ON wp_ai_trainer_text(created_at);
CREATE INDEX idx_interactions_created_at ON wp_ai_trainer_interactions(created_at);
```

### 5. Monitoring Setup

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Configure log rotation
sudo nano /etc/logrotate.d/wordpress

# Add configuration
/var/www/html/wordpress/wp-content/debug.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 644 www-data www-data
}
```

## WordPress.org Distribution

### 1. Prepare Distribution Package

```bash
# Create distribution directory
mkdir ai-trainer-dist
cd ai-trainer-dist

# Copy plugin files
cp -r ../rag-exa-plugin/* .

# Remove development files
rm -rf .git/
rm -rf node_modules/
rm -rf vendor/
rm -f .env
rm -f .env.example
rm -f composer.lock
rm -f package-lock.json
rm -f webpack.config.js
rm -f .eslintrc.js
rm -f .prettierrc

# Create zip file
zip -r ai-trainer-1.1.0.zip .
```

### 2. WordPress.org Guidelines

- **Readme.txt**: Must follow WordPress.org format
- **License**: Must be GPL compatible
- **Code Quality**: Must pass WordPress coding standards
- **Security**: Must follow WordPress security guidelines
- **Documentation**: Must include comprehensive documentation

### 3. Submission Process

1. **Create Account**: Register on WordPress.org
2. **Submit Plugin**: Use the plugin submission form
3. **Review Process**: Wait for review (2-4 weeks)
4. **Approval**: Plugin approved and published
5. **Updates**: Submit updates through SVN

## Docker Deployment

### 1. Dockerfile

```dockerfile
# Dockerfile for AI Trainer Plugin
FROM wordpress:latest

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs

# Copy plugin files
COPY . /usr/src/wordpress/wp-content/plugins/ai-trainer/

# Set working directory
WORKDIR /usr/src/wordpress/wp-content/plugins/ai-trainer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

# Set permissions
RUN chown -R www-data:www-data /usr/src/wordpress/wp-content/plugins/ai-trainer

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
```

### 2. Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  wordpress:
    build: .
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DEBUG: 1
    volumes:
      - wordpress_data:/var/www/html
      - ./uploads.ini:/usr/local/etc/php/conf.d/uploads.ini
    depends_on:
      - db

  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: somewordpress
    volumes:
      - db_data:/var/lib/mysql

volumes:
  wordpress_data:
  db_data:
```

### 3. Deployment Commands

```bash
# Build and start containers
docker-compose up -d

# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Update plugin
docker-compose exec wordpress wp plugin update ai-trainer
```

## Cloud Deployment

### AWS Deployment

#### 1. EC2 Instance Setup

```bash
# Launch EC2 instance
aws ec2 run-instances \
    --image-id ami-0c55b159cbfafe1f0 \
    --count 1 \
    --instance-type t3.medium \
    --key-name your-key-pair \
    --security-group-ids sg-xxxxxxxxx

# Connect to instance
ssh -i your-key.pem ubuntu@your-instance-ip
```

#### 2. Application Load Balancer

```bash
# Create ALB
aws elbv2 create-load-balancer \
    --name ai-trainer-alb \
    --subnets subnet-xxxxxxxxx subnet-yyyyyyyyy \
    --security-groups sg-xxxxxxxxx

# Create target group
aws elbv2 create-target-group \
    --name ai-trainer-tg \
    --protocol HTTP \
    --port 80 \
    --vpc-id vpc-xxxxxxxxx
```

#### 3. Auto Scaling Group

```bash
# Create launch template
aws ec2 create-launch-template \
    --launch-template-name ai-trainer-lt \
    --version-description v1 \
    --launch-template-data '{"ImageId":"ami-xxxxxxxxx","InstanceType":"t3.medium"}'

# Create auto scaling group
aws autoscaling create-auto-scaling-group \
    --auto-scaling-group-name ai-trainer-asg \
    --launch-template LaunchTemplateName=ai-trainer-lt \
    --min-size 2 \
    --max-size 10 \
    --desired-capacity 2
```

### Google Cloud Platform

#### 1. Compute Engine

```bash
# Create instance
gcloud compute instances create ai-trainer-instance \
    --zone=us-central1-a \
    --machine-type=e2-medium \
    --image-family=debian-10 \
    --image-project=debian-cloud

# Install WordPress
gcloud compute ssh ai-trainer-instance --zone=us-central1-a
```

#### 2. Load Balancer

```bash
# Create load balancer
gcloud compute forwarding-rules create ai-trainer-lb \
    --global \
    --target-http-proxy=ai-trainer-proxy \
    --ports=80,443
```

### Azure Deployment

#### 1. Virtual Machine

```bash
# Create VM
az vm create \
    --resource-group ai-trainer-rg \
    --name ai-trainer-vm \
    --image UbuntuLTS \
    --size Standard_B2s \
    --admin-username azureuser \
    --generate-ssh-keys
```

#### 2. Application Gateway

```bash
# Create application gateway
az network application-gateway create \
    --resource-group ai-trainer-rg \
    --name ai-trainer-ag \
    --vnet-name ai-trainer-vnet \
    --subnet agsubnet
```

## Performance Optimization

### 1. Caching Strategy

```php
// wp-config.php
define('WP_CACHE', true);

// Install caching plugin
// W3 Total Cache or WP Rocket recommended
```

### 2. Database Optimization

```sql
-- Optimize database queries
SET SESSION query_cache_type = ON;
SET SESSION query_cache_size = 67108864;

-- Create indexes for frequently queried columns
CREATE INDEX idx_qa_question ON wp_ai_trainer_qa(question(255));
CREATE INDEX idx_text_title ON wp_ai_trainer_text(title(255));
```

### 3. CDN Configuration

```php
// Add CDN support
define('CDN_URL', 'https://cdn.yourdomain.com');

// Update asset URLs
function ai_trainer_cdn_url($url) {
    if (strpos($url, '/wp-content/') !== false) {
        return str_replace(site_url(), CDN_URL, $url);
    }
    return $url;
}
add_filter('wp_get_attachment_url', 'ai_trainer_cdn_url');
```

### 4. Asset Optimization

```bash
# Minify CSS and JS
npm run build:production

# Optimize images
find assets/images -name "*.png" -exec optipng {} \;
find assets/images -name "*.jpg" -exec jpegoptim {} \;
```

## Monitoring and Logging

### 1. Application Monitoring

```php
// Add monitoring hooks
add_action('ai_trainer_api_call', function($api, $response_time) {
    error_log("AI Trainer API Call: $api - {$response_time}ms");
});

add_action('ai_trainer_error', function($error, $context) {
    error_log("AI Trainer Error: $error - " . json_encode($context));
});
```

### 2. Performance Monitoring

```bash
# Install monitoring tools
sudo apt install -y apache2-utils siege

# Test performance
ab -n 1000 -c 10 http://yourdomain.com/

# Monitor resource usage
htop
iotop
nethogs
```

### 3. Log Analysis

```bash
# Analyze access logs
tail -f /var/log/apache2/access.log | grep ai-trainer

# Monitor error logs
tail -f /var/log/apache2/error.log

# Analyze plugin logs
tail -f /var/www/html/wordpress/wp-content/debug.log
```

## Backup and Recovery

### 1. Database Backup

```bash
# Create backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u wordpress -p wordpress > backup_$DATE.sql
gzip backup_$DATE.sql

# Schedule daily backups
crontab -e
# Add: 0 2 * * * /path/to/backup-script.sh
```

### 2. File Backup

```bash
# Backup plugin files
tar -czf ai-trainer-backup-$DATE.tar.gz /var/www/html/wordpress/wp-content/plugins/ai-trainer/

# Backup uploads
tar -czf uploads-backup-$DATE.tar.gz /var/www/html/wordpress/wp-content/uploads/
```

### 3. Disaster Recovery

```bash
# Restore database
mysql -u wordpress -p wordpress < backup_20250101_120000.sql

# Restore files
tar -xzf ai-trainer-backup-20250101_120000.tar.gz -C /

# Verify restoration
wp plugin list --status=active
wp db check
```

## Troubleshooting

### Common Issues

#### 1. API Connection Errors

```php
// Check API connectivity
function ai_trainer_test_api_connection() {
    $openai_key = get_option('ai_trainer_openai_key');
    $response = wp_remote_get('https://api.openai.com/v1/models', array(
        'headers' => array('Authorization' => 'Bearer ' . $openai_key)
    ));
    
    if (is_wp_error($response)) {
        error_log('OpenAI API connection failed: ' . $response->get_error_message());
        return false;
    }
    
    return true;
}
```

#### 2. Database Connection Issues

```sql
-- Check database connectivity
SELECT 1;

-- Check table structure
DESCRIBE wp_ai_trainer_qa;
DESCRIBE wp_ai_trainer_text;

-- Check for errors
SHOW ENGINE INNODB STATUS;
```

#### 3. Memory Issues

```php
// Increase memory limit
define('WP_MEMORY_LIMIT', '512M');

// Monitor memory usage
function ai_trainer_memory_usage() {
    $memory_usage = memory_get_usage(true);
    error_log("Memory usage: " . ($memory_usage / 1024 / 1024) . " MB");
}
```

### Debug Mode

```php
// Enable debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Add custom debug logging
function ai_trainer_debug_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("AI Trainer Debug: " . $message);
    }
}
```

### Support Resources

- **Documentation**: README.md, API_DOCUMENTATION.md
- **GitHub Issues**: https://github.com/samnguyen92/rag-exa-plugin/issues
- **WordPress Support**: WordPress.org plugin support forums
- **Email Support**: support@psychedelic.com

---

## Deployment Checklist

### Pre-Deployment

- [ ] **System Requirements**: Verify all requirements are met
- [ ] **API Keys**: Obtain and test API keys
- [ ] **Backup**: Create backup of existing system
- [ ] **Testing**: Test in staging environment
- [ ] **Documentation**: Review deployment documentation

### Deployment

- [ ] **Installation**: Install plugin files
- [ ] **Dependencies**: Install all dependencies
- [ ] **Configuration**: Configure plugin settings
- [ ] **Database**: Create/update database tables
- [ ] **Permissions**: Set correct file permissions
- [ ] **SSL**: Configure SSL certificate

### Post-Deployment

- [ ] **Testing**: Test all functionality
- [ ] **Performance**: Monitor performance metrics
- [ ] **Security**: Verify security measures
- [ ] **Backup**: Create post-deployment backup
- [ ] **Monitoring**: Set up monitoring and alerts
- [ ] **Documentation**: Update deployment documentation

---

*This deployment guide is part of the AI Trainer Dashboard plugin v1.1.0. For more information, see the README.md file.*
