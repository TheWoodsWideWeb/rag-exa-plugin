<?php
// Ensure ABSPATH is defined for includes
if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__, 5) . '/');
if (!function_exists('sanitize_text_field')) require_once(ABSPATH . 'wp-includes/formatting.php');
if (!function_exists('esc_url')) require_once(ABSPATH . 'wp-includes/formatting.php');
if (!function_exists('wp_nonce_field')) require_once(ABSPATH . 'wp-includes/functions.php');

?>
<h2>Website Management</h2>
<p>Configure websites for AI training with granular control over crawling scope and subpage targeting.</p>
<div id="website-notices"></div>

<!-- Add Website Form -->
<div class="website-form-container" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h3 style="margin-top: 0;">Add New Website</h3>
    <form id="add-website-form" method="post">
        <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px; align-items: end;">
            <div style="flex: 1;">
                <label for="website_title" style="display: block; margin-bottom: 5px; font-weight: 600;">Website Title</label>
                <input type="text" id="website_title" name="website_title" placeholder="e.g., Reddit Microdosing" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
            </div>
            <div style="flex: 1;">
                <label for="website_url" style="display: block; margin-bottom: 5px; font-weight: 600;">URL</label>
                <input type="url" id="website_url" name="website_url" placeholder="https://example.com" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
            </div>
            <div style="flex: 0 0 200px;">
                <label for="website_tier" style="display: block; margin-bottom: 5px; font-weight: 600;">Priority Tier</label>
                <select id="website_tier" name="website_tier" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                    <option value="">Select Tier</option>
                    <option value="1">Tier 1 (Highest Priority)</option>
                    <option value="2">Tier 2 (High Priority)</option>
                    <option value="3">Tier 3 (Medium Priority)</option>
                    <option value="4">Tier 4 (Low Priority)</option>
                </select>
            </div>
        </div>
        
        <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px; align-items: end;">
            <div style="flex: 1;">
                <label for="crawl_scope" style="display: block; margin-bottom: 5px; font-weight: 600;">Crawl Scope</label>
                <select id="crawl_scope" name="crawl_scope" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                    <option value="entire_site">Entire Site</option>
                    <option value="subpages_only">Subpages Only</option>
                    <option value="specific_paths">Specific Paths</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label for="subpage_depth" style="display: block; margin-bottom: 5px; font-weight: 600;">Subpage Depth</label>
                <input type="number" id="subpage_depth" name="subpage_depth" min="1" max="100" value="5" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666; font-size: 12px;">Maximum number of subpages to crawl (1-100)</small>
            </div>
            <div style="flex: 1;">
                <label for="subpage_keywords" style="display: block; margin-bottom: 5px; font-weight: 600;">Subpage Target Keywords</label>
                <input type="text" id="subpage_keywords" name="subpage_keywords" placeholder="e.g., discussion, post, thread, article" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666; font-size: 12px;">Enter keywords separated by commas to prioritize specific types of subpages</small>
            </div>
        </div>
        
        <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px; align-items: end;">
            <div style="flex: 1;">
                <label for="path_patterns" style="display: block; margin-bottom: 5px; font-weight: 600;">Path Patterns</label>
                <input type="text" id="path_patterns" name="path_patterns" placeholder="e.g., /r/microdosing/*, /r/PsilocybinMushrooms/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666; font-size: 12px;">Enter specific URL patterns to crawl (use * for wildcards). Be specific to avoid crawling entire domains.</small>
            </div>
        </div>
        
        <div class="form-row" style="margin-bottom: 15px;">
            <button type="submit" class="button button-primary" style="padding: 10px 20px; font-size: 14px;">Add Website</button>
        </div>
    </form>
</div>

<div id="website-sources-table"></div>

<!-- Enhanced Edit Modal for Website -->
<div id="website-edit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; min-width: 500px; max-width: 80%; max-height: 80%; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; color: #333;">Edit Website</h3>
        <form id="edit-website-form" method="post">
            <input type="hidden" name="website_id" id="edit-website-id">
            
            <div style="margin-bottom: 20px;">
                <label for="edit-website-title" style="display: block; margin-bottom: 5px; font-weight: 600;">Title:</label>
                <input type="text" id="edit-website-title" name="website_title" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-website-url" style="display: block; margin-bottom: 5px; font-weight: 600;">URL:</label>
                <input type="url" id="edit-website-url" name="website_url" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-website-tier" style="display: block; margin-bottom: 5px; font-weight: 600;">Tier:</label>
                <select id="edit-website-tier" name="website_tier" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;" required>
                    <option value="1">Tier 1 (Highest Priority)</option>
                    <option value="2">Tier 2 (High Priority)</option>
                    <option value="3">Tier 3 (Medium Priority)</option>
                    <option value="4">Tier 4 (Low Priority)</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-crawl-scope" style="display: block; margin-bottom: 5px; font-weight: 600;">Crawl Scope:</label>
                <select id="edit-crawl-scope" name="crawl_scope" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;" required>
                    <option value="entire_site">Entire Site</option>
                    <option value="subpages_only">Subpages Only</option>
                    <option value="specific_paths">Specific Paths</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-subpage-depth" style="display: block; margin-bottom: 5px; font-weight: 600;">Subpage Depth:</label>
                <input type="number" id="edit-subpage-depth" name="subpage_depth" min="1" max="100" value="5" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 5px;">
                <small style="color: #666; font-size: 12px;">Maximum number of subpages to crawl (1-100)</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-subpage-keywords" style="display: block; margin-bottom: 5px; font-weight: 600;">Subpage Target Keywords:</label>
                <input type="text" id="edit-subpage-keywords" name="subpage_keywords" placeholder="e.g., discussion, post, thread, article" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 5px;">
                <small style="color: #666; font-size: 12px;">Enter keywords separated by commas to prioritize specific types of subpages</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="edit-path-patterns" style="display: block; margin-bottom: 5px; font-weight: 600;">Path Patterns:</label>
                <input type="text" id="edit-path-patterns" name="path_patterns" placeholder="e.g., /r/microdosing/*, /r/PsilocybinMushrooms/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 5px;">
                <small style="color: #666; font-size: 12px;">Enter specific URL patterns to crawl (use * for wildcards). Be specific to avoid crawling entire domains.</small>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="button close-website-modal" style="padding: 8px 16px;">Cancel</button>
                <button type="submit" class="button button-primary" style="padding: 8px 16px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(function($){
    // On page load, load the website table via AJAX
    if ($('#website-sources-table').length) {
        $.post(ai_trainer_ajax.ajaxurl, { action: 'ai_get_website_table', nonce: ai_trainer_ajax.nonce }, function (response) {
            if (response.html) $('#website-sources-table').html(response.html);
            if (response.notice) {
                $('#website-notices').html(response.notice).show();
                setTimeout(function() { $('#website-notices').fadeOut(); }, 3000);
            }
        }, 'json');
    }
    
    // Handle form field dependencies
    $('#crawl_scope, #edit-crawl-scope').on('change', function() {
        var scope = $(this).val();
        var form = $(this).closest('form');
        var keywordsField = form.find('[name="subpage_keywords"]');
        var patternsField = form.find('[name="path_patterns"]');
        var depthField = form.find('[name="subpage_depth"]');
        
        if (scope === 'entire_site') {
            keywordsField.prop('required', false).closest('div').hide();
            patternsField.prop('required', false).closest('div').hide();
            depthField.prop('required', false).closest('div').hide();
        } else if (scope === 'subpages_only') {
            keywordsField.prop('required', true).closest('div').show();
            patternsField.prop('required', false).closest('div').hide();
            depthField.prop('required', true).closest('div').show();
        } else if (scope === 'specific_paths') {
            keywordsField.prop('required', false).closest('div').show();
            patternsField.prop('required', true).closest('div').show();
            depthField.prop('required', true).closest('div').show();
        }
    });
    
    // Trigger initial state
    $('#crawl_scope').trigger('change');
    
    // Validate path patterns for overly broad patterns
    $(document).on('input', '[name="path_patterns"]', function() {
        var patterns = $(this).val();
        var warningDiv = $(this).siblings('.pattern-warning');
        
        if (!warningDiv.length) {
            warningDiv = $('<div class="pattern-warning" style="color: #d63384; font-size: 11px; margin-top: 2px;"></div>');
            $(this).after(warningDiv);
        }
        
        if (patterns) {
            var patternArray = patterns.split(',').map(function(p) { return p.trim(); });
            var broadPatterns = patternArray.filter(function(p) {
                return p === '/*' || p === '/' || p === '*' || p.length < 3;
            });
            
            if (broadPatterns.length > 0) {
                warningDiv.html('⚠️ Warning: Pattern(s) "' + broadPatterns.join(', ') + '" may crawl entire domains. Use specific patterns like /r/subreddit/*');
                warningDiv.show();
            } else {
                warningDiv.hide();
            }
        } else {
            warningDiv.hide();
        }
    });
});
</script> 