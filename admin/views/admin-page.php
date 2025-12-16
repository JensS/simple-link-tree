<div class="wrap slt-admin-wrap">
    <h1>Simple Linktree Settings</h1>

    <div class="slt-admin-container">
        <div class="slt-settings-section">
            <form method="post" action="">
                <?php wp_nonce_field('slt_settings_nonce'); ?>

                <!-- General Settings -->
                <div class="slt-collapsible">
                    <button type="button" class="slt-collapsible-header">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span>General Settings</span>
                    </button>
                    <div class="slt-collapsible-content">
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
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="slt-collapsible">
                    <button type="button" class="slt-collapsible-header">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span>SEO Settings</span>
                    </button>
                    <div class="slt-collapsible-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="slt_seo_indexable">Search Engine Indexing</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="slt_seo_indexable" name="slt_seo_indexable" value="1" <?php checked($seo_settings['indexable'], '1'); ?> />
                                        Allow search engines to index this page
                                    </label>
                                    <p class="description">When enabled, removes noindex/nofollow and allows Google to index your linktree</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_meta_description">Meta Description</label>
                                </th>
                                <td>
                                    <textarea id="slt_meta_description" name="slt_meta_description" rows="2" class="large-text" maxlength="160"><?php echo esc_textarea($seo_settings['meta_description']); ?></textarea>
                                    <p class="description">SEO description for search results (max 160 characters). Leave empty to auto-generate from bio.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_og_image">Social Share Image</label>
                                </th>
                                <td>
                                    <input type="url" id="slt_og_image" name="slt_og_image" value="<?php echo esc_attr($seo_settings['og_image']); ?>" class="large-text" placeholder="https://example.com/image.jpg" />
                                    <p class="description">Image URL for Open Graph / Twitter Cards (recommended: 1200x630px)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Geographic / Language Settings -->
                <div class="slt-collapsible">
                    <button type="button" class="slt-collapsible-header">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span>Geographic / Language Settings</span>
                    </button>
                    <div class="slt-collapsible-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="slt_language">Language</label>
                                </th>
                                <td>
                                    <select id="slt_language" name="slt_language">
                                        <option value="en" <?php selected($seo_settings['language'], 'en'); ?>>English (en)</option>
                                        <option value="de" <?php selected($seo_settings['language'], 'de'); ?>>German (de)</option>
                                        <option value="fr" <?php selected($seo_settings['language'], 'fr'); ?>>French (fr)</option>
                                        <option value="es" <?php selected($seo_settings['language'], 'es'); ?>>Spanish (es)</option>
                                        <option value="it" <?php selected($seo_settings['language'], 'it'); ?>>Italian (it)</option>
                                        <option value="pt" <?php selected($seo_settings['language'], 'pt'); ?>>Portuguese (pt)</option>
                                        <option value="nl" <?php selected($seo_settings['language'], 'nl'); ?>>Dutch (nl)</option>
                                        <option value="pl" <?php selected($seo_settings['language'], 'pl'); ?>>Polish (pl)</option>
                                        <option value="ru" <?php selected($seo_settings['language'], 'ru'); ?>>Russian (ru)</option>
                                        <option value="ja" <?php selected($seo_settings['language'], 'ja'); ?>>Japanese (ja)</option>
                                        <option value="zh" <?php selected($seo_settings['language'], 'zh'); ?>>Chinese (zh)</option>
                                        <option value="ko" <?php selected($seo_settings['language'], 'ko'); ?>>Korean (ko)</option>
                                        <option value="ar" <?php selected($seo_settings['language'], 'ar'); ?>>Arabic (ar)</option>
                                        <option value="sv" <?php selected($seo_settings['language'], 'sv'); ?>>Swedish (sv)</option>
                                        <option value="da" <?php selected($seo_settings['language'], 'da'); ?>>Danish (da)</option>
                                        <option value="no" <?php selected($seo_settings['language'], 'no'); ?>>Norwegian (no)</option>
                                        <option value="fi" <?php selected($seo_settings['language'], 'fi'); ?>>Finnish (fi)</option>
                                    </select>
                                    <p class="description">Primary language for hreflang attribute</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_geo_region">Geographic Region</label>
                                </th>
                                <td>
                                    <select id="slt_geo_region" name="slt_geo_region">
                                        <option value="" <?php selected($seo_settings['geo_region'], ''); ?>>— Select Region —</option>
                                        <optgroup label="North America">
                                            <option value="US" <?php selected($seo_settings['geo_region'], 'US'); ?>>United States (US)</option>
                                            <option value="CA" <?php selected($seo_settings['geo_region'], 'CA'); ?>>Canada (CA)</option>
                                            <option value="MX" <?php selected($seo_settings['geo_region'], 'MX'); ?>>Mexico (MX)</option>
                                        </optgroup>
                                        <optgroup label="Europe">
                                            <option value="DE" <?php selected($seo_settings['geo_region'], 'DE'); ?>>Germany (DE)</option>
                                            <option value="GB" <?php selected($seo_settings['geo_region'], 'GB'); ?>>United Kingdom (GB)</option>
                                            <option value="FR" <?php selected($seo_settings['geo_region'], 'FR'); ?>>France (FR)</option>
                                            <option value="ES" <?php selected($seo_settings['geo_region'], 'ES'); ?>>Spain (ES)</option>
                                            <option value="IT" <?php selected($seo_settings['geo_region'], 'IT'); ?>>Italy (IT)</option>
                                            <option value="NL" <?php selected($seo_settings['geo_region'], 'NL'); ?>>Netherlands (NL)</option>
                                            <option value="BE" <?php selected($seo_settings['geo_region'], 'BE'); ?>>Belgium (BE)</option>
                                            <option value="AT" <?php selected($seo_settings['geo_region'], 'AT'); ?>>Austria (AT)</option>
                                            <option value="CH" <?php selected($seo_settings['geo_region'], 'CH'); ?>>Switzerland (CH)</option>
                                            <option value="PL" <?php selected($seo_settings['geo_region'], 'PL'); ?>>Poland (PL)</option>
                                            <option value="SE" <?php selected($seo_settings['geo_region'], 'SE'); ?>>Sweden (SE)</option>
                                            <option value="NO" <?php selected($seo_settings['geo_region'], 'NO'); ?>>Norway (NO)</option>
                                            <option value="DK" <?php selected($seo_settings['geo_region'], 'DK'); ?>>Denmark (DK)</option>
                                            <option value="FI" <?php selected($seo_settings['geo_region'], 'FI'); ?>>Finland (FI)</option>
                                            <option value="PT" <?php selected($seo_settings['geo_region'], 'PT'); ?>>Portugal (PT)</option>
                                            <option value="IE" <?php selected($seo_settings['geo_region'], 'IE'); ?>>Ireland (IE)</option>
                                        </optgroup>
                                        <optgroup label="Asia Pacific">
                                            <option value="AU" <?php selected($seo_settings['geo_region'], 'AU'); ?>>Australia (AU)</option>
                                            <option value="NZ" <?php selected($seo_settings['geo_region'], 'NZ'); ?>>New Zealand (NZ)</option>
                                            <option value="JP" <?php selected($seo_settings['geo_region'], 'JP'); ?>>Japan (JP)</option>
                                            <option value="CN" <?php selected($seo_settings['geo_region'], 'CN'); ?>>China (CN)</option>
                                            <option value="KR" <?php selected($seo_settings['geo_region'], 'KR'); ?>>South Korea (KR)</option>
                                            <option value="IN" <?php selected($seo_settings['geo_region'], 'IN'); ?>>India (IN)</option>
                                            <option value="SG" <?php selected($seo_settings['geo_region'], 'SG'); ?>>Singapore (SG)</option>
                                        </optgroup>
                                        <optgroup label="South America">
                                            <option value="BR" <?php selected($seo_settings['geo_region'], 'BR'); ?>>Brazil (BR)</option>
                                            <option value="AR" <?php selected($seo_settings['geo_region'], 'AR'); ?>>Argentina (AR)</option>
                                        </optgroup>
                                    </select>
                                    <p class="description">ISO 3166-1 country code for geo meta tags</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_geo_placename">Place Name</label>
                                </th>
                                <td>
                                    <input type="text" id="slt_geo_placename" name="slt_geo_placename" value="<?php echo esc_attr($seo_settings['geo_placename']); ?>" class="regular-text" placeholder="Los Angeles" />
                                    <p class="description">City or location name for geo meta tags</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Schema.org Structured Data -->
                <div class="slt-collapsible">
                    <button type="button" class="slt-collapsible-header">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span>Schema.org Structured Data</span>
                    </button>
                    <div class="slt-collapsible-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="slt_schema_type">Entity Type</label>
                                </th>
                                <td>
                                    <select id="slt_schema_type" name="slt_schema_type">
                                        <option value="Person" <?php selected($seo_settings['schema_type'], 'Person'); ?>>Person</option>
                                        <option value="Organization" <?php selected($seo_settings['schema_type'], 'Organization'); ?>>Organization</option>
                                    </select>
                                    <p class="description">Choose whether this linktree represents a person or organization</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_schema_location">Location / City</label>
                                </th>
                                <td>
                                    <input type="text" id="slt_schema_location" name="slt_schema_location" value="<?php echo esc_attr($seo_settings['schema_location']); ?>" class="regular-text" placeholder="Los Angeles" />
                                    <p class="description">City/locality for Schema.org location data</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="slt_schema_country">Country</label>
                                </th>
                                <td>
                                    <input type="text" id="slt_schema_country" name="slt_schema_country" value="<?php echo esc_attr($seo_settings['schema_country']); ?>" class="regular-text" placeholder="United States" />
                                    <p class="description">Country name for Schema.org location data</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

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

        <div class="slt-statistics-section">
            <h2>Statistics</h2>
            <p class="slt-stats-note">GDPR-compliant tracking (no cookies, no personal data stored)</p>

            <div class="slt-stats-overview">
                <div class="slt-stat-box">
                    <div class="slt-stat-value"><?php echo number_format($statistics['total_views']); ?></div>
                    <div class="slt-stat-label">Total Page Views</div>
                </div>
                <div class="slt-stat-box">
                    <div class="slt-stat-value"><?php echo number_format($statistics['total_clicks']); ?></div>
                    <div class="slt-stat-label">Total Link Clicks</div>
                </div>
                <div class="slt-stat-box">
                    <div class="slt-stat-value">
                        <?php
                        if ($statistics['total_views'] > 0) {
                            echo number_format(($statistics['total_clicks'] / $statistics['total_views']) * 100, 1) . '%';
                        } else {
                            echo '0%';
                        }
                        ?>
                    </div>
                    <div class="slt-stat-label">Click-Through Rate</div>
                </div>
            </div>

            <?php if (!empty($statistics['link_stats'])): ?>
            <div class="slt-stats-table">
                <h3>Link Performance</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Link Title</th>
                            <th>URL</th>
                            <th style="text-align: right;">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics['link_stats'] as $link_stat): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($link_stat['title']); ?></strong>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($link_stat['url']); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($link_stat['url']); ?>
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <strong><?php echo number_format($link_stat['clicks']); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($statistics['daily_views'])): ?>
            <div class="slt-stats-chart">
                <h3>Recent Activity (Last 30 Days)</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th style="text-align: right;">Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics['daily_views'] as $daily): ?>
                        <tr>
                            <td><?php echo esc_html(date('F j, Y', strtotime($daily['event_date']))); ?></td>
                            <td style="text-align: right;">
                                <strong><?php echo number_format($daily['views']); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


