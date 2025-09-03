# API Documentation

## AI Trainer Dashboard Plugin API Reference

This document provides comprehensive documentation for all API endpoints, functions, and integration points of the AI Trainer Dashboard plugin.

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Core Functions](#core-functions)
- [Database Operations](#database-operations)
- [OpenAI Integration](#openai-integration)
- [Exa.ai Integration](#exaai-integration)
- [Admin Functions](#admin-functions)
- [Frontend Functions](#frontend-functions)
- [Hooks and Filters](#hooks-and-filters)
- [Error Handling](#error-handling)
- [Examples](#examples)
- [Rate Limits](#rate-limits)
- [Security](#security)

## Overview

The AI Trainer Dashboard plugin provides a comprehensive API for managing AI-powered knowledge bases, content processing, and search functionality. The API is built on WordPress standards and provides both PHP functions and REST endpoints.

### Base URL

```
https://your-domain.com/wp-content/plugins/rag-exa-plugin/
```

### API Version

Current version: `1.1.0`

## Authentication

### WordPress Nonces

All AJAX requests require WordPress nonces for security:

```php
// Generate nonce
$nonce = wp_create_nonce('ai_trainer_action');

// Verify nonce
if (!wp_verify_nonce($_POST['nonce'], 'ai_trainer_action')) {
    wp_die('Security check failed');
}
```

### Capability Checks

Admin functions require proper WordPress capabilities:

```php
// Check admin capabilities
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}
```

### API Key Authentication

External API calls use stored API keys:

```php
// Get OpenAI API key
$openai_key = get_option('ai_trainer_openai_key');

// Get Exa.ai API key
$exa_key = get_option('ai_trainer_exa_key');
```

## Core Functions

### Plugin Initialization

#### `ai_trainer_init()`

Initializes the plugin and sets up all necessary components.

```php
/**
 * Initialize AI Trainer plugin
 * 
 * @since 1.1.0
 * @return void
 */
function ai_trainer_init() {
    // Plugin initialization logic
}
```

**Usage:**
```php
// Called automatically on plugin load
add_action('init', 'ai_trainer_init');
```

### Database Setup

#### `ai_trainer_create_tables()`

Creates all necessary database tables for the plugin.

```php
/**
 * Create database tables for AI Trainer
 * 
 * @since 1.1.0
 * @return bool True on success, false on failure
 */
function ai_trainer_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Q&A table
    $table_qa = $wpdb->prefix . 'ai_trainer_qa';
    $sql_qa = "CREATE TABLE $table_qa (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        question text NOT NULL,
        answer longtext NOT NULL,
        embedding longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    // Additional tables...
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_qa);
    
    return true;
}
```

## Database Operations

### Q&A Management

#### `ai_trainer_add_qa($question, $answer)`

Adds a new Q&A entry to the knowledge base.

```php
/**
 * Add Q&A entry to knowledge base
 * 
 * @since 1.1.0
 * @param string $question The question text
 * @param string $answer The answer text
 * @return int|WP_Error Entry ID on success, WP_Error on failure
 */
function ai_trainer_add_qa($question, $answer) {
    global $wpdb;
    
    // Validate inputs
    if (empty($question) || empty($answer)) {
        return new WP_Error('invalid_input', 'Question and answer cannot be empty');
    }
    
    // Sanitize inputs
    $question = sanitize_textarea_field($question);
    $answer = wp_kses_post($answer);
    
    // Generate embedding
    $embedding = ai_trainer_generate_embedding($question);
    if (is_wp_error($embedding)) {
        return $embedding;
    }
    
    // Insert into database
    $result = $wpdb->insert(
        $wpdb->prefix . 'ai_trainer_qa',
        array(
            'question' => $question,
            'answer' => $answer,
            'embedding' => json_encode($embedding)
        ),
        array('%s', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to insert Q&A entry');
    }
    
    return $wpdb->insert_id;
}
```

**Usage:**
```php
$qa_id = ai_trainer_add_qa(
    'What is LSD?',
    'LSD (Lysergic acid diethylamide) is a psychedelic drug...'
);

if (is_wp_error($qa_id)) {
    error_log('Failed to add Q&A: ' . $qa_id->get_error_message());
}
```

#### `ai_trainer_get_qa($id)`

Retrieves a Q&A entry by ID.

```php
/**
 * Get Q&A entry by ID
 * 
 * @since 1.1.0
 * @param int $id The Q&A entry ID
 * @return object|WP_Error Q&A object on success, WP_Error on failure
 */
function ai_trainer_get_qa($id) {
    global $wpdb;
    
    $id = absint($id);
    
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_trainer_qa WHERE id = %d",
            $id
        )
    );
    
    if (!$result) {
        return new WP_Error('not_found', 'Q&A entry not found');
    }
    
    return $result;
}
```

#### `ai_trainer_update_qa($id, $question, $answer)`

Updates an existing Q&A entry.

```php
/**
 * Update Q&A entry
 * 
 * @since 1.1.0
 * @param int $id The Q&A entry ID
 * @param string $question The updated question
 * @param string $answer The updated answer
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ai_trainer_update_qa($id, $question, $answer) {
    global $wpdb;
    
    $id = absint($id);
    
    // Validate inputs
    if (empty($question) || empty($answer)) {
        return new WP_Error('invalid_input', 'Question and answer cannot be empty');
    }
    
    // Sanitize inputs
    $question = sanitize_textarea_field($question);
    $answer = wp_kses_post($answer);
    
    // Generate new embedding
    $embedding = ai_trainer_generate_embedding($question);
    if (is_wp_error($embedding)) {
        return $embedding;
    }
    
    // Update database
    $result = $wpdb->update(
        $wpdb->prefix . 'ai_trainer_qa',
        array(
            'question' => $question,
            'answer' => $answer,
            'embedding' => json_encode($embedding)
        ),
        array('id' => $id),
        array('%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to update Q&A entry');
    }
    
    return true;
}
```

#### `ai_trainer_delete_qa($id)`

Deletes a Q&A entry.

```php
/**
 * Delete Q&A entry
 * 
 * @since 1.1.0
 * @param int $id The Q&A entry ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ai_trainer_delete_qa($id) {
    global $wpdb;
    
    $id = absint($id);
    
    $result = $wpdb->delete(
        $wpdb->prefix . 'ai_trainer_qa',
        array('id' => $id),
        array('%d')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to delete Q&A entry');
    }
    
    return true;
}
```

#### `ai_trainer_get_all_qa($limit = 50, $offset = 0)`

Retrieves all Q&A entries with pagination.

```php
/**
 * Get all Q&A entries with pagination
 * 
 * @since 1.1.0
 * @param int $limit Number of entries to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of Q&A objects
 */
function ai_trainer_get_all_qa($limit = 50, $offset = 0) {
    global $wpdb;
    
    $limit = absint($limit);
    $offset = absint($offset);
    
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ai_trainer_qa 
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        )
    );
    
    return $results ?: array();
}
```

### Text Content Management

#### `ai_trainer_add_text($title, $content)`

Adds text content to the knowledge base.

```php
/**
 * Add text content to knowledge base
 * 
 * @since 1.1.0
 * @param string $title The content title
 * @param string $content The content text
 * @return int|WP_Error Content ID on success, WP_Error on failure
 */
function ai_trainer_add_text($title, $content) {
    global $wpdb;
    
    // Validate inputs
    if (empty($title) || empty($content)) {
        return new WP_Error('invalid_input', 'Title and content cannot be empty');
    }
    
    // Sanitize inputs
    $title = sanitize_text_field($title);
    $content = wp_kses_post($content);
    
    // Generate embedding from title and content
    $embedding_text = $title . ' ' . $content;
    $embedding = ai_trainer_generate_embedding($embedding_text);
    if (is_wp_error($embedding)) {
        return $embedding;
    }
    
    // Insert into database
    $result = $wpdb->insert(
        $wpdb->prefix . 'ai_trainer_text',
        array(
            'title' => $title,
            'content' => $content,
            'embedding' => json_encode($embedding)
        ),
        array('%s', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to insert text content');
    }
    
    return $wpdb->insert_id;
}
```

### File Management

#### `ai_trainer_process_file($file_path, $file_type)`

Processes uploaded files and extracts content.

```php
/**
 * Process uploaded file and extract content
 * 
 * @since 1.1.0
 * @param string $file_path Path to uploaded file
 * @param string $file_type Type of file (pdf, txt, docx)
 * @return array|WP_Error Extracted content array or WP_Error on failure
 */
function ai_trainer_process_file($file_path, $file_type) {
    // Validate file exists
    if (!file_exists($file_path)) {
        return new WP_Error('file_not_found', 'File does not exist');
    }
    
    // Process based on file type
    switch ($file_type) {
        case 'pdf':
            return ai_trainer_extract_pdf_content($file_path);
        case 'txt':
            return ai_trainer_extract_txt_content($file_path);
        case 'docx':
            return ai_trainer_extract_docx_content($file_path);
        default:
            return new WP_Error('unsupported_type', 'Unsupported file type');
    }
}
```

## OpenAI Integration

### Embedding Generation

#### `ai_trainer_generate_embedding($text)`

Generates OpenAI embeddings for text content.

```php
/**
 * Generate OpenAI embedding for text
 * 
 * @since 1.1.0
 * @param string $text Text to generate embedding for
 * @return array|WP_Error Embedding array or WP_Error on failure
 */
function ai_trainer_generate_embedding($text) {
    $api_key = get_option('ai_trainer_openai_key');
    
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'OpenAI API key not configured');
    }
    
    // Validate API key format
    if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $api_key)) {
        return new WP_Error('invalid_api_key', 'Invalid OpenAI API key format');
    }
    
    $url = 'https://api.openai.com/v1/embeddings';
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    );
    
    $data = array(
        'input' => $text,
        'model' => 'text-embedding-ada-002'
    );
    
    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($data),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Failed to connect to OpenAI API');
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (isset($result['error'])) {
        return new WP_Error('api_error', $result['error']['message']);
    }
    
    if (!isset($result['data'][0]['embedding'])) {
        return new WP_Error('api_error', 'Invalid response from OpenAI API');
    }
    
    return $result['data'][0]['embedding'];
}
```

### Semantic Search

#### `ai_trainer_semantic_search($query, $limit = 5)`

Performs semantic search using embeddings.

```php
/**
 * Perform semantic search using embeddings
 * 
 * @since 1.1.0
 * @param string $query Search query
 * @param int $limit Number of results to return
 * @return array|WP_Error Search results or WP_Error on failure
 */
function ai_trainer_semantic_search($query, $limit = 5) {
    global $wpdb;
    
    // Generate embedding for query
    $query_embedding = ai_trainer_generate_embedding($query);
    if (is_wp_error($query_embedding)) {
        return $query_embedding;
    }
    
    // Search in Q&A table
    $qa_results = ai_trainer_search_qa_embeddings($query_embedding, $limit);
    if (is_wp_error($qa_results)) {
        return $qa_results;
    }
    
    // Search in text table
    $text_results = ai_trainer_search_text_embeddings($query_embedding, $limit);
    if (is_wp_error($text_results)) {
        return $text_results;
    }
    
    // Combine and rank results
    $all_results = array_merge($qa_results, $text_results);
    usort($all_results, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    return array_slice($all_results, 0, $limit);
}
```

## Exa.ai Integration

### Neural Search

#### `ai_trainer_exa_search($query, $num_results = 5)`

Performs neural search using Exa.ai API.

```php
/**
 * Perform neural search using Exa.ai API
 * 
 * @since 1.1.0
 * @param string $query Search query
 * @param int $num_results Number of results to return
 * @return array|WP_Error Search results or WP_Error on failure
 */
function ai_trainer_exa_search($query, $num_results = 5) {
    $api_key = get_option('ai_trainer_exa_key');
    
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'Exa.ai API key not configured');
    }
    
    $url = 'https://api.exa.ai/search';
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    );
    
    $data = array(
        'query' => $query,
        'numResults' => $num_results,
        'includeDomains' => array('psychedelics.com'),
        'useAutoprompt' => true
    );
    
    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($data),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Failed to connect to Exa.ai API');
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (isset($result['error'])) {
        return new WP_Error('api_error', $result['error']['message']);
    }
    
    return $result['results'] ?? array();
}
```

## Admin Functions

### Settings Management

#### `ai_trainer_get_settings()`

Retrieves all plugin settings.

```php
/**
 * Get all plugin settings
 * 
 * @since 1.1.0
 * @return array Settings array
 */
function ai_trainer_get_settings() {
    return array(
        'openai_key' => get_option('ai_trainer_openai_key', ''),
        'exa_key' => get_option('ai_trainer_exa_key', ''),
        'openai_model' => get_option('ai_trainer_openai_model', 'gpt-3.5-turbo'),
        'embedding_model' => get_option('ai_trainer_embedding_model', 'text-embedding-ada-002'),
        'max_results' => get_option('ai_trainer_max_results', 5),
        'auto_page_template' => get_option('ai_trainer_auto_page_template', ''),
        'enable_analytics' => get_option('ai_trainer_enable_analytics', true)
    );
}
```

#### `ai_trainer_update_settings($settings)`

Updates plugin settings.

```php
/**
 * Update plugin settings
 * 
 * @since 1.1.0
 * @param array $settings Settings array
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ai_trainer_update_settings($settings) {
    $valid_settings = array(
        'openai_key',
        'exa_key',
        'openai_model',
        'embedding_model',
        'max_results',
        'auto_page_template',
        'enable_analytics'
    );
    
    foreach ($settings as $key => $value) {
        if (!in_array($key, $valid_settings)) {
            continue;
        }
        
        $option_name = 'ai_trainer_' . $key;
        update_option($option_name, sanitize_text_field($value));
    }
    
    return true;
}
```

### Analytics

#### `ai_trainer_log_interaction($query, $response, $feedback = null)`

Logs user interactions for analytics.

```php
/**
 * Log user interaction for analytics
 * 
 * @since 1.1.0
 * @param string $query User query
 * @param string $response System response
 * @param string|null $feedback User feedback (like/dislike)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function ai_trainer_log_interaction($query, $response, $feedback = null) {
    global $wpdb;
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'ai_trainer_interactions',
        array(
            'query' => sanitize_text_field($query),
            'response' => wp_kses_post($response),
            'feedback' => $feedback ? sanitize_text_field($feedback) : null,
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%d', '%s', '%s')
    );
    
    return $result !== false;
}
```

## Frontend Functions

### Shortcode Functions

#### `ai_trainer_chat_shortcode($atts)`

Renders the AI chat interface.

```php
/**
 * AI chat shortcode
 * 
 * @since 1.1.0
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function ai_trainer_chat_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'AI Assistant',
        'placeholder' => 'Ask me anything...',
        'max_length' => 500
    ), $atts);
    
    ob_start();
    ?>
    <div class="ai-trainer-chat" data-max-length="<?php echo esc_attr($atts['max_length']); ?>">
        <div class="ai-trainer-chat-header">
            <h3><?php echo esc_html($atts['title']); ?></h3>
        </div>
        <div class="ai-trainer-chat-messages" id="ai-trainer-messages"></div>
        <div class="ai-trainer-chat-input">
            <textarea 
                id="ai-trainer-input" 
                placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                maxlength="<?php echo esc_attr($atts['max_length']); ?>"
            ></textarea>
            <button id="ai-trainer-send">Send</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
```

### AJAX Handlers

#### `ai_trainer_ajax_chat()`

Handles AJAX chat requests.

```php
/**
 * AJAX handler for chat requests
 * 
 * @since 1.1.0
 * @return void
 */
function ai_trainer_ajax_chat() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ai_trainer_chat')) {
        wp_die('Security check failed');
    }
    
    $query = sanitize_textarea_field($_POST['query']);
    
    if (empty($query)) {
        wp_send_json_error('Query cannot be empty');
    }
    
    // Perform search
    $results = ai_trainer_semantic_search($query);
    if (is_wp_error($results)) {
        wp_send_json_error($results->get_error_message());
    }
    
    // Generate response
    $response = ai_trainer_generate_response($query, $results);
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }
    
    // Log interaction
    ai_trainer_log_interaction($query, $response);
    
    wp_send_json_success(array(
        'response' => $response,
        'sources' => $results
    ));
}
```

## Hooks and Filters

### Action Hooks

#### `ai_trainer_after_qa_added`

Fired after a Q&A entry is added.

```php
/**
 * Action hook: After Q&A entry is added
 * 
 * @since 1.1.0
 * @param int $qa_id The Q&A entry ID
 * @param string $question The question text
 * @param string $answer The answer text
 */
do_action('ai_trainer_after_qa_added', $qa_id, $question, $answer);
```

#### `ai_trainer_after_search`

Fired after a search is performed.

```php
/**
 * Action hook: After search is performed
 * 
 * @since 1.1.0
 * @param string $query The search query
 * @param array $results The search results
 */
do_action('ai_trainer_after_search', $query, $results);
```

### Filter Hooks

#### `ai_trainer_search_results`

Filters search results before returning.

```php
/**
 * Filter hook: Search results
 * 
 * @since 1.1.0
 * @param array $results The search results
 * @param string $query The search query
 * @return array Modified search results
 */
$results = apply_filters('ai_trainer_search_results', $results, $query);
```

#### `ai_trainer_response_text`

Filters AI response text before returning.

```php
/**
 * Filter hook: AI response text
 * 
 * @since 1.1.0
 * @param string $response The AI response text
 * @param string $query The original query
 * @return string Modified response text
 */
$response = apply_filters('ai_trainer_response_text', $response, $query);
```

## Error Handling

### Error Types

The plugin uses WordPress error handling with custom error codes:

- `invalid_input`: Invalid input parameters
- `no_api_key`: Missing API key
- `invalid_api_key`: Invalid API key format
- `api_error`: External API error
- `db_error`: Database operation error
- `file_not_found`: File not found
- `unsupported_type`: Unsupported file type
- `not_found`: Resource not found

### Error Handling Example

```php
$result = ai_trainer_add_qa($question, $answer);

if (is_wp_error($result)) {
    switch ($result->get_error_code()) {
        case 'invalid_input':
            $message = 'Please provide both question and answer.';
            break;
        case 'no_api_key':
            $message = 'OpenAI API key not configured.';
            break;
        case 'api_error':
            $message = 'API error: ' . $result->get_error_message();
            break;
        default:
            $message = 'An error occurred: ' . $result->get_error_message();
    }
    
    error_log('AI Trainer Error: ' . $result->get_error_message());
    // Handle error appropriately
}
```

## Examples

### Complete Q&A Management Example

```php
// Add Q&A entry
$qa_id = ai_trainer_add_qa(
    'What are the effects of psilocybin?',
    'Psilocybin is a psychedelic compound found in certain mushrooms...'
);

if (is_wp_error($qa_id)) {
    error_log('Failed to add Q&A: ' . $qa_id->get_error_message());
} else {
    echo "Q&A added with ID: $qa_id";
}

// Search for similar content
$results = ai_trainer_semantic_search('psilocybin effects', 3);

if (is_wp_error($results)) {
    error_log('Search failed: ' . $results->get_error_message());
} else {
    foreach ($results as $result) {
        echo "Similarity: " . $result['similarity'] . "\n";
        echo "Content: " . $result['content'] . "\n";
    }
}
```

### Frontend Integration Example

```php
// Add shortcode to page
echo do_shortcode('[ai_trainer_chat title="Psychedelic Assistant" placeholder="Ask about psychedelics..."]');

// Enqueue scripts
wp_enqueue_script('ai-trainer-frontend', plugin_dir_url(__FILE__) . 'assets/js/exa.js', array('jquery'), '1.1.0', true);

// Localize script with AJAX URL
wp_localize_script('ai-trainer-frontend', 'aiTrainerAjax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('ai_trainer_chat')
));
```

### Custom Hook Example

```php
// Add custom action after Q&A is added
add_action('ai_trainer_after_qa_added', function($qa_id, $question, $answer) {
    // Send notification
    wp_mail(
        get_option('admin_email'),
        'New Q&A Added',
        "A new Q&A entry was added:\n\nQuestion: $question\nAnswer: $answer"
    );
}, 10, 3);

// Filter search results
add_filter('ai_trainer_search_results', function($results, $query) {
    // Add custom ranking logic
    foreach ($results as &$result) {
        if (strpos($result['content'], 'psychedelic') !== false) {
            $result['similarity'] *= 1.2; // Boost psychedelic-related content
        }
    }
    
    return $results;
}, 10, 2);
```

## Rate Limits

### OpenAI API Limits

- **Embeddings**: 3,000 requests per minute
- **Chat Completions**: 3,500 requests per minute
- **Rate Limit Headers**: Check `X-RateLimit-*` headers

### Exa.ai API Limits

- **Search Requests**: 1,000 requests per minute
- **Rate Limit Headers**: Check `X-RateLimit-*` headers

### Handling Rate Limits

```php
function ai_trainer_handle_rate_limit($response) {
    $headers = wp_remote_retrieve_headers($response);
    
    if (isset($headers['X-RateLimit-Remaining']) && $headers['X-RateLimit-Remaining'] < 10) {
        // Log rate limit warning
        error_log('AI Trainer: Approaching rate limit');
    }
    
    if (wp_remote_retrieve_response_code($response) === 429) {
        // Rate limit exceeded
        $retry_after = $headers['Retry-After'] ?? 60;
        sleep($retry_after);
        return false; // Indicate retry needed
    }
    
    return true;
}
```

## Security

### Input Validation

All inputs are validated and sanitized:

```php
// Text input validation
$text = sanitize_textarea_field($_POST['text']);

// File upload validation
$allowed_types = array('pdf', 'txt', 'docx');
$file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
if (!in_array($file_extension, $allowed_types)) {
    wp_die('Invalid file type');
}
```

### SQL Injection Prevention

All database queries use prepared statements:

```php
$stmt = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ai_trainer_qa WHERE id = %d",
    $id
);
```

### XSS Prevention

All output is properly escaped:

```php
echo esc_html($data);
echo esc_attr($data);
echo wp_kses_post($data);
```

### API Key Security

API keys are validated and stored securely:

```php
// Validate API key format
if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $api_key)) {
    return new WP_Error('invalid_api_key', 'Invalid API key format');
}
```

---

## Support

For API support and questions:

- **Documentation**: This file and inline code comments
- **GitHub Issues**: https://github.com/samnguyen92/rag-exa-plugin/issues
- **Email Support**: support@psychedelic.com
- **WordPress Forums**: WordPress.org plugin support

---

*This API documentation is part of the AI Trainer Dashboard plugin v1.1.0. For more information, see the README.md file.*
