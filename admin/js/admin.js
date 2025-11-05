jQuery(document).ready(function($) {
    'use strict';
    
    // Make links sortable
    $('#links-container').sortable({
        handle: '.slt-link-handle',
        placeholder: 'slt-link-placeholder',
        axis: 'y',
        opacity: 0.8,
        cursor: 'move',
        update: function(event, ui) {
            // Links reordered
        }
    });
    
    // Update slug preview in real-time
    $('#slt_page_slug').on('input', function() {
        const slug = $(this).val();
        $('#slug-preview').text(slug);
    });
    
    // Add new link
    $('#add-link-btn').on('click', function() {
        const template = $('#link-item-template').html();
        const uniqueId = 'link-' + Date.now();
        const newLink = template.replace(/{{id}}/g, uniqueId);
        
        $('.slt-no-links').remove();
        $('#links-container').append(newLink);
        
        // Refresh sortable
        $('#links-container').sortable('refresh');
        
        // Focus on the new link's title field
        $('#links-container .slt-link-item:last-child .link-title').focus();
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
