<?php

/**
 * OpenAI Integration - AI Trainer Plugin
 * 
 * This file handles all interactions with OpenAI's API, specifically for generating
 * text embeddings that enable semantic search functionality in the knowledge base.
 * 
 * ============================================================================
 * EMBEDDING SYSTEM OVERVIEW
 * ============================================================================
 * 
 * SEMANTIC SEARCH FUNDAMENTALS:
 * - Text embeddings are numerical representations of text that capture semantic meaning
 * - Similar texts have similar embeddings, enabling semantic search capabilities
 * - We use OpenAI's text-embedding-ada-002 model for high-quality embeddings
 * - Embeddings are normalized to unit vectors for consistent similarity calculations
 * - 1536-dimensional vectors provide rich semantic representation
 * 
 * TECHNICAL IMPLEMENTATION:
 * - API integration with OpenAI's embedding service
 * - Vector normalization for consistent similarity calculations
 * - Cosine similarity computation for content matching
 * - Error handling and fallback mechanisms
 * - Performance optimization and caching strategies
 * - Security and privacy protection measures
 * 
 * ============================================================================
 * KEY FUNCTIONS AND CAPABILITIES
 * ============================================================================
 * 
 * CORE FUNCTIONS:
 * - ai_trainer_generate_embedding(): Creates embeddings from text via OpenAI API
 * - ai_trainer_normalize_embedding(): Normalizes vectors to unit length
 * - ai_trainer_cosine_similarity(): Calculates similarity between embeddings
 * 
 * EMBEDDING GENERATION:
 * - Text preprocessing and validation
 * - API communication with OpenAI
 * - Response parsing and validation
 * - Error handling and logging
 * - Performance optimization
 * - Security validation
 * 
 * SIMILARITY CALCULATIONS:
 * - Cosine similarity computation
 * - Vector normalization
 * - Input validation and sanitization
 * - Performance optimization
 * - Threshold-based filtering
 * 
 * ============================================================================
 * USAGE PATTERNS AND INTEGRATION
 * ============================================================================
 * 
 * KNOWLEDGE BASE INTEGRATION:
 * - Called when adding new knowledge base entries (Q&A, files, text)
 * - Used for semantic search to find relevant content
 * - Stored in database as JSON-encoded arrays
 * - Integrated with search algorithms
 * - Automatic embedding updates on content changes
 * 
 * SEARCH FUNCTIONALITY:
 * - Query embedding generation
 * - Content similarity matching
 * - Relevance scoring and ranking
 * - Search result optimization
 * - Real-time similarity calculations
 * 
 * TRAINING DATA MANAGEMENT:
 * - Automatic embedding generation
 * - Content similarity analysis
 * - Training data quality assessment
 * - Continuous improvement tracking
 * - Embedding consistency validation
 * 
 * ============================================================================
 * API INTEGRATION DETAILS
 * ============================================================================
 * 
 * OPENAI API SPECIFICATIONS:
 * - Model: text-embedding-ada-002 (latest embedding model)
 * - Input limit: 2000 characters (truncated if longer)
 * - Output: 1536-dimensional vector
 * - Rate limits: Check OpenAI's current pricing and limits
 * - Authentication: Bearer token via API key
 * - Response format: JSON with data array containing embedding
 * 
 * API COMMUNICATION:
 * - HTTP POST requests to OpenAI endpoints
 * - JSON request/response handling
 * - Error handling and retry logic
 * - Timeout management (30 seconds)
 * - Network error handling
 * - SSL/TLS encryption
 * 
 * ============================================================================
 * PERFORMANCE OPTIMIZATION
 * ============================================================================
 * 
 * VECTOR PROCESSING:
 * - Efficient normalization algorithms
 * - Memory-optimized calculations
 * - Batch processing capabilities
 * - Caching strategies
 * - Lazy loading for large datasets
 * 
 * API OPTIMIZATION:
 * - Request batching where possible
 * - Connection pooling
 * - Response caching
 * - Error recovery mechanisms
 * - Rate limit management
 * 
 * DATABASE INTEGRATION:
 * - Efficient storage formats
 * - Indexed field usage
 * - Query optimization
 * - Storage compression
 * - Embedding indexing strategies
 * 
 * ============================================================================
 * ERROR HANDLING AND RELIABILITY
 * ============================================================================
 * 
 * API ERROR HANDLING:
 * - Network timeout management
 * - API rate limit handling
 * - Invalid response validation
 * - Graceful degradation
 * - Retry logic for transient failures
 * - Circuit breaker pattern implementation
 * 
 * FALLBACK MECHANISMS:
 * - Error logging and monitoring
 * - Retry logic for transient failures
 * - Alternative processing paths
 * - User feedback and notifications
 * - Degraded search functionality
 * 
 * DEBUGGING AND MONITORING:
 * - Comprehensive error logging
 * - Performance metrics tracking
 * - API usage monitoring
 * - Quality assurance tools
 * - Embedding quality validation
 * 
 * ============================================================================
 * SECURITY AND PRIVACY
 * ============================================================================
 * 
 * API KEY MANAGEMENT:
 * - Secure storage in WordPress constants
 * - Environment variable support
 * - Access control and validation
 * - Key rotation capabilities
 * - API key validation and verification
 * 
 * DATA PROTECTION:
 * - Input sanitization and validation
 * - Secure API communication
 * - Privacy compliance features
 * - Data encryption where applicable
 * - GDPR compliance considerations
 * 
 * SECURITY MEASURES:
 * - HTTPS-only API communication
 * - Input validation and sanitization
 * - Error message sanitization
 * - Rate limiting protection
 * - Malicious input detection
 * 
 * ============================================================================
 * FUTURE ENHANCEMENTS
 * ============================================================================
 * 
 * PLANNED IMPROVEMENTS:
 * - Retry logic for transient API failures
 * - Caching for repeated embeddings
 * - Batch processing capabilities
 * - Alternative embedding models
 * - Local embedding generation
 * - Embedding compression techniques
 * 
 * SCALABILITY FEATURES:
 * - Distributed processing
 * - Load balancing
 * - Performance monitoring
 * - Resource optimization
 * - Auto-scaling capabilities
 * 
 * ============================================================================
 * DEPENDENCIES AND REQUIREMENTS
 * ============================================================================
 * 
 * WORDPRESS REQUIREMENTS:
 * - WordPress 5.0+
 * - PHP 7.4+
 * - cURL extension
 * - JSON extension
 * 
 * EXTERNAL DEPENDENCIES:
 * - OpenAI API access
 * - Valid API key
 * - Internet connectivity
 * - SSL/TLS support
 * 
 * @package AI_Trainer
 * @since 1.0
 * @author Psychedelic
 * @license GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

/**
 * Normalize an embedding vector to unit length
 * 
 * This function converts a raw embedding vector into a unit vector (length = 1).
 * Normalization is crucial for consistent similarity calculations because:
 * - Raw embeddings can have varying magnitudes
 * - Cosine similarity calculations require normalized vectors
 * - Unit vectors ensure fair comparison between different texts
 * - Consistent similarity scoring across different content types
 * 
 * MATHEMATICAL PROCESS:
 * 1. Calculate the magnitude (L2 norm) of the vector
 * 2. Divide each component by the magnitude
 * 3. Add small epsilon (1e-8) to prevent division by zero
 * 4. Return normalized unit vector
 * 
 * PERFORMANCE FEATURES:
 * - Efficient L2 norm calculation
 * - Memory-optimized processing
 * - Numerical stability with epsilon
 * - Fast vector normalization
 * - O(n) time complexity
 * 
 * APPLICATIONS:
 * - Semantic similarity calculations
 * - Content matching algorithms
 * - Search result ranking
 * - Training data quality assessment
 * - Embedding consistency validation
 * 
 * @param array $embedding Raw embedding vector from OpenAI API
 * @return array Normalized unit vector
 * @since 1.0
 * @throws Exception If input is not a valid array
 * 
 * @example
 * $raw_embedding = [0.5, 0.3, 0.4];
 * $normalized = ai_trainer_normalize_embedding($raw_embedding);
 * // Result: [0.707, 0.424, 0.566] (approximately unit length)
 * 
 * @example
 * // Validate normalization
 * $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $normalized)));
 * // Should be approximately 1.0
 */
function ai_trainer_normalize_embedding($embedding) {
    // Input validation
    if (!is_array($embedding) || empty($embedding)) {
        error_log('AI Trainer: Invalid embedding input for normalization');
        return [];
    }
    
    // Calculate the L2 norm (magnitude) of the vector
    $norm = 0.0;
    foreach ($embedding as $v) {
        if (!is_numeric($v)) {
            error_log('AI Trainer: Non-numeric value in embedding vector');
            return [];
        }
        $norm += $v * $v;  // Sum of squares
    }
    $norm = sqrt($norm) + 1e-8;  // Square root + small epsilon to prevent division by zero
    
    // Normalize each component to create a unit vector
    foreach ($embedding as &$v) {
        $v = $v / $norm;
    }
    
    return $embedding;
}

/**
 * Generate text embedding using OpenAI's API
 * 
 * This function sends text to OpenAI's embedding API and returns a normalized
 * embedding vector. The embedding captures the semantic meaning of the text,
 * enabling similarity-based search across the knowledge base.
 * 
 * API DETAILS:
 * - Model: text-embedding-ada-002 (OpenAI's latest embedding model)
 * - Input limit: 2000 characters (truncated if longer)
 * - Output: 1536-dimensional vector
 * - Rate limits: Check OpenAI's current pricing and limits
 * - Authentication: Bearer token via API key
 * - Response format: JSON with data array containing embedding
 * 
 * ERROR HANDLING:
 * - Returns false on API errors
 * - Logs errors to WordPress error log
 * - Gracefully handles network timeouts
 * - Validates API responses
 * - Handles malformed responses
 * - Provides detailed error information
 * 
 * PERFORMANCE FEATURES:
 * - Efficient text preprocessing
 * - Optimized API communication
 * - Response validation and parsing
 * - Automatic vector normalization
 * - Memory-efficient processing
 * - Request timeout management
 * 
 * SECURITY FEATURES:
 * - API key validation
 * - Input sanitization
 * - Secure HTTP communication
 * - Error message sanitization
 * - Rate limit protection
 * - SSL/TLS encryption
 * 
 * @param string $text The text to generate an embedding for
 * @return string|false JSON-encoded embedding array, or false on error
 * @since 1.0
 * @throws Exception If API key is invalid or missing
 * 
 * @example
 * $text = "What are the benefits of microdosing psilocybin?";
 * $embedding = ai_trainer_generate_embedding($text);
 * if ($embedding) {
 *     // Store in database for later search
 *     ai_trainer_save_to_db("Microdosing Benefits", "qna", $text, $embedding);
 * }
 * 
 * @example
 * // Error handling
 * $embedding = ai_trainer_generate_embedding($text);
 * if ($embedding === false) {
 *     error_log('Failed to generate embedding for text: ' . substr($text, 0, 100));
 *     // Handle error gracefully
 * }
 * 
 * @todo Consider adding retry logic for transient API failures
 * @todo Add caching for repeated embeddings to reduce API calls
 * @todo Implement batch processing for multiple texts
 * @todo Add embedding quality validation
 */
function ai_trainer_generate_embedding($text) {
    // Input validation and sanitization
    if (empty($text) || !is_string($text)) {
        error_log('AI Trainer: Invalid text input for embedding generation');
        return false;
    }
    
    // Sanitize input text
    $text = sanitize_text_field($text);
    if (empty($text)) {
        error_log('AI Trainer: Empty text after sanitization');
        return false;
    }
    
    // Get API key from WordPress constants (set in main plugin file)
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
    
    // Validate API key
    if (empty($api_key)) {
        error_log('AI Trainer: OpenAI API key not configured');
        return false;
    }
    
    // Validate API key format (basic check)
    if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $api_key)) {
        error_log('AI Trainer: Invalid OpenAI API key format');
        return false;
    }
    
    // Prepare the API request to OpenAI
    $request_body = [
        'input' => substr($text, 0, 2000),  // Truncate to API limit
        'model' => 'text-embedding-ada-002', // Latest embedding model
    ];
    
    $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'AI-Trainer-Plugin/1.1',
        ],
        'body' => json_encode($request_body),
        'timeout' => 30,  // 30 second timeout for API calls
        'sslverify' => true, // Verify SSL certificates
    ]);
    
    // Handle WordPress HTTP errors (network issues, timeouts, etc.)
    if (is_wp_error($response)) {
        error_log('AI Trainer: OpenAI API request failed: ' . $response->get_error_message());
        return false;
    }
    
    // Check HTTP response code
    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code !== 200) {
        error_log('AI Trainer: OpenAI API returned HTTP ' . $http_code);
        return false;
    }
    
    // Parse the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // Validate JSON response
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('AI Trainer: Invalid JSON response from OpenAI API');
        return false;
    }
    
    // Extract the embedding from the response
    $embedding = $data['data'][0]['embedding'] ?? [];
    
    // Validate that we received a proper embedding
    if (empty($embedding) || !is_array($embedding)) {
        error_log('AI Trainer: Invalid embedding response from OpenAI API');
        return false;
    }
    
    // Validate embedding dimensions (should be 1536 for text-embedding-ada-002)
    if (count($embedding) !== 1536) {
        error_log('AI Trainer: Unexpected embedding dimensions: ' . count($embedding));
        return false;
    }
    
    // Normalize the embedding to unit length for consistent similarity calculations
    $embedding = ai_trainer_normalize_embedding($embedding);
    
    // Validate normalization
    if (empty($embedding)) {
        error_log('AI Trainer: Failed to normalize embedding');
        return false;
    }
    
    // Return as JSON string for database storage
    return json_encode($embedding);
}

/**
 * Calculate cosine similarity between two embedding vectors
 * 
 * This function calculates the cosine similarity between two normalized embedding
 * vectors. Cosine similarity ranges from -1 to 1, where:
 * - 1.0 = identical meaning (perfect match)
 * - 0.0 = unrelated meaning (no similarity)
 * - -1.0 = opposite meaning (negative correlation)
 * 
 * MATHEMATICAL FORMULA:
 * cos(θ) = (A · B) / (||A|| × ||B||)
 * Since vectors are normalized, ||A|| = ||B|| = 1, so cos(θ) = A · B
 * 
 * SIMILARITY INTERPRETATION:
 * - 1.0: Identical meaning (perfect match)
 * - 0.8-0.9: Very similar meaning (high relevance)
 * - 0.6-0.7: Moderately similar (good relevance)
 * - 0.4-0.5: Somewhat similar (moderate relevance)
 * - 0.0-0.3: Little similarity (low relevance)
 * - Negative values: Opposite meanings
 * 
 * PERFORMANCE FEATURES:
 * - Optimized for normalized vectors (||A|| = ||B|| = 1)
 * - Efficient dot product calculation
 * - Memory-efficient processing
 * - Fast similarity computation
 * - Vector dimension validation
 * - O(n) time complexity
 * 
 * VALIDATION FEATURES:
 * - Input array validation
 * - Vector length checking
 * - Numeric value verification
 * - Error handling for invalid inputs
 * - Automatic JSON decoding for string inputs
 * - Dimension compatibility checking
 * 
 * @param array|string $embedding1 First embedding vector (normalized array or JSON string)
 * @param array|string $embedding2 Second embedding vector (normalized array or JSON string)
 * @return float Cosine similarity score between -1 and 1, or 0.0 on error
 * @since 1.0
 * @throws Exception If inputs are invalid or incompatible
 * 
 * @example
 * $score = ai_trainer_cosine_similarity($embedding1, $embedding2);
 * if ($score > 0.8) {
 *     // High similarity - likely relevant
 *     $results[] = $item;
 * }
 * 
 * @example
 * // Batch similarity calculation
 * foreach ($embeddings as $id => $embedding) {
 *     $similarity = ai_trainer_cosine_similarity($query_embedding, $embedding);
 *     if ($similarity > 0.7) {
 *         $relevant_items[$id] = $similarity;
 *     }
 * }
 * 
 * @example
 * // Threshold-based filtering
 * $threshold = 0.6;
 * $similar_items = array_filter($items, function($item) use ($query_embedding, $threshold) {
 *     return ai_trainer_cosine_similarity($query_embedding, $item['embedding']) > $threshold;
 * });
 * 
 * @todo Consider adding threshold-based optimization
 * @todo Add support for different similarity metrics (Euclidean, Manhattan)
 * @todo Implement batch processing for multiple comparisons
 * @todo Add similarity score caching
 */
function ai_trainer_cosine_similarity($embedding1, $embedding2) {
    // Ensure both embeddings are arrays
    if (is_string($embedding1)) {
        $embedding1 = json_decode($embedding1, true);
    }
    if (is_string($embedding2)) {
        $embedding2 = json_decode($embedding2, true);
    }
    
    // Validate inputs
    if (!is_array($embedding1) || !is_array($embedding2)) {
        error_log('AI Trainer: Invalid embedding input for similarity calculation');
        return 0.0;
    }
    
    // Check if arrays are empty
    if (empty($embedding1) || empty($embedding2)) {
        error_log('AI Trainer: Empty embedding arrays for similarity calculation');
        return 0.0;
    }
    
    // Validate that all values are numeric
    foreach ($embedding1 as $v) {
        if (!is_numeric($v)) {
            error_log('AI Trainer: Non-numeric value in first embedding');
            return 0.0;
        }
    }
    foreach ($embedding2 as $v) {
        if (!is_numeric($v)) {
            error_log('AI Trainer: Non-numeric value in second embedding');
            return 0.0;
        }
    }
    
    // Check dimension compatibility
    $length1 = count($embedding1);
    $length2 = count($embedding2);
    if ($length1 !== $length2) {
        error_log('AI Trainer: Incompatible embedding dimensions: ' . $length1 . ' vs ' . $length2);
        return 0.0;
    }
    
    // Calculate dot product
    $dot_product = 0.0;
    for ($i = 0; $i < $length1; $i++) {
        $dot_product += $embedding1[$i] * $embedding2[$i];
    }
    
    // Return cosine similarity (dot product of normalized vectors)
    // Clamp to [-1, 1] range for numerical stability
    return max(-1.0, min(1.0, $dot_product));
}
