jQuery(document).ready(function($) {
    'use strict';
    
    const linksContainer = document.getElementById('links-container');
    if (linksContainer) {
        new Sortable(linksContainer, {
            animation: 150,
            handle: '.slt-link-handle',
            ghostClass: 'slt-link-placeholder',
            onEnd: function () {
                // Links reordered
            }
        });
    }
    
    // Update slug preview in real-time
    $('#slt_page_slug').on('input', function() {
        const slug = $(this).val();
        $('#slug-preview').text(slug);
    });
    
    // Add new link
    $('#add-link-btn').on('click', function() {
        const uniqueId = 'link-' + Date.now();
        const newLink = $('<div class="slt-link-item" data-id="' + uniqueId + '">' +
            '<div class="slt-link-handle"><span class="dashicons dashicons-menu"></span></div>' +
            '<div class="slt-link-content">' +
                '<div class="slt-link-field"><label>Title</label><input type="text" class="link-title" value="" placeholder="Link Title" /></div>' +
                '<div class="slt-link-field"><label>URL</label><input type="url" class="link-url" value="" placeholder="https://example.com" /></div>' +
                '<div class="slt-link-field slt-link-icon-field"><label>Icon (optional)</label><input type="text" class="link-icon" value="" placeholder="e.g., 🔗 or emoji" /></div>' +
            '</div>' +
            '<div class="slt-link-actions"><button type="button" class="button delete-link-btn" title="Delete"><span class="dashicons dashicons-trash"></span></button></div>' +
        '</div>');

        $('.slt-no-links').remove();
        $('#links-container').append(newLink);
        
        // Focus on the new link's title field
        newLink.find('.link-title').focus();
    });
    
    // Delete link
    $(document).on('click', '.delete-link-btn', function() {
        if (confirm('Are you sure you want to delete this link?')) {
            const $linkItem = $(this).closest('.slt-link-item');
            
            $linkItem.fadeOut(300, function() {
                $(this).remove();
                
                // Show "no links" message if all links are deleted
                if ($('#links-container .slt-link-item').length === 0) {
                    $('#links-container').html('<p class="slt-no-links">No links yet. Click "Add New Link" to get started!</p>');
                }
            });
        }
    });
    
    // Save all links
    $('#save-links-btn').on('click', function() {
        const $btn = $(this);
        const $status = $('.slt-save-status');
        
        $btn.prop('disabled', true).text('Saving...');
        $status.text('');
        
        const links = [];
        
        $('#links-container .slt-link-item').each(function() {
            const $item = $(this);
            const link = {
                id: $item.data('id'),
                title: $item.find('.link-title').val().trim(),
                url: $item.find('.link-url').val().trim(),
                icon: $item.find('.link-icon').val().trim()
            };
            
            // Only add links with at least a title and URL
            if (link.title && link.url) {
                links.push(link);
            }
        });
        
        $.ajax({
            url: sltAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'slt_save_links',
                nonce: sltAdmin.nonce,
                links: links
            },
            success: function(response) {
                if (response.success) {
                    $status.text('✓ Links saved successfully!').removeClass('error');
                    
                    // Clear status after 3 seconds
                    setTimeout(function() {
                        $status.text('');
                    }, 3000);
                } else {
                    $status.text('Error: ' + response.data).addClass('error');
                }
            },
            error: function() {
                $status.text('Error: Failed to save links').addClass('error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save All Links');
            }
        });
    });
    
    // Auto-save on Ctrl+S / Cmd+S
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('#save-links-btn').click();
        }
    });
    
    // Validate URLs on blur
    $(document).on('blur', '.link-url', function() {
        const $input = $(this);
        const url = $input.val().trim();
        
        if (url && !url.match(/^https?:\/\//)) {
            $input.val('https://' + url);
        }
    });
});
