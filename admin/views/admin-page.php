<div class="wrap slt-admin-wrap">
    <h1>Simple Linktree Settings</h1>
    
    <div class="slt-admin-container">
        <div class="slt-settings-section">
            <h2>General Settings</h2>
            <form method="post" action="">
                <?php wp_nonce_field('slt_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="slt_page_slug">Page Slug</label>
                        </th>
                        <td>
                            <input type="text" id="slt_page_slug" name="slt_page_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" />
                            <p class="description">Your linktree will be accessible at: <strong><?php echo home_url('/'); ?><span id="slug-preview"><?php echo esc_html($slug); ?></span></strong></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="slt_profile_name">Profile Name</label>
                        </th>
                        <td>
                            <input type="text" id="slt_profile_name" name="slt_profile_name" value="<?php echo esc_attr($profile_name); ?>" class="regular-text" />
                            <p class="description">Displayed at the top of your linktree page</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="slt_profile_bio">Profile Bio</label>
                        </th>
                        <td>
                            <textarea id="slt_profile_bio" name="slt_profile_bio" rows="3" class="large-text"><?php echo esc_textarea($profile_bio); ?></textarea>
                            <p class="description">Optional bio text below your name</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="slt_update_slug" class="button button-primary" value="Save Settings" />
                </p>
            </form>
        </div>
        
        <div class="slt-links-section">
            <div class="slt-links-header">
                <h2>Manage Links</h2>
                <button type="button" class="button button-primary" id="add-link-btn">Add New Link</button>
            </div>
            
            <div id="links-container" class="slt-links-list">
                <?php if (!empty($links)): ?>
                    <?php foreach ($links as $link): ?>
                        <div class="slt-link-item" data-id="<?php echo esc_attr($link['id']); ?>">
                            <div class="slt-link-handle">
                                <span class="dashicons dashicons-menu"></span>
                            </div>
                            <div class="slt-link-content">
                                <div class="slt-link-field">
                                    <label>Title</label>
                                    <input type="text" class="link-title" value="<?php echo esc_attr($link['title']); ?>" placeholder="Link Title" />
                                </div>
                                <div class="slt-link-field">
                                    <label>URL</label>
                                    <input type="url" class="link-url" value="<?php echo esc_attr($link['url']); ?>" placeholder="https://example.com" />
                                </div>
                                <div class="slt-link-field slt-link-icon-field">
                                    <label>Icon (optional)</label>
                                    <input type="text" class="link-icon" value="<?php echo esc_attr($link['icon']); ?>" placeholder="e.g., 🔗 or emoji" />
                                </div>
                            </div>
                            <div class="slt-link-actions">
                                <button type="button" class="button delete-link-btn" title="Delete">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="slt-no-links">No links yet. Click "Add New Link" to get started!</p>
                <?php endif; ?>
            </div>
            
            <div class="slt-links-actions">
                <button type="button" class="button button-primary button-large" id="save-links-btn">Save All Links</button>
                <span class="slt-save-status"></span>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="link-item-template">
    <div class="slt-link-item" data-id="{{id}}">
        <div class="slt-link-handle">
            <span class="dashicons dashicons-menu"></span>
        </div>
        <div class="slt-link-content">
            <div class="slt-link-field">
                <label>Title</label>
                <input type="text" class="link-title" value="" placeholder="Link Title" />
            </div>
            <div class="slt-link-field">
                <label>URL</label>
                <input type="url" class="link-url" value="" placeholder="https://example.com" />
            </div>
            <div class="slt-link-field slt-link-icon-field">
                <label>Icon (optional)</label>
                <input type="text" class="link-icon" value="" placeholder="e.g., 🔗 or emoji" />
            </div>
        </div>
        <div class="slt-link-actions">
            <button type="button" class="button delete-link-btn" title="Delete">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
    </div>
</script>
