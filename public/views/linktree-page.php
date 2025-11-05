<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($profile_name); ?> - Links</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #000000;
            --text-secondary: #666666;
            --link-bg: #f5f5f5;
            --link-hover: #e8e8e8;
            --border-color: #e0e0e0;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #0a0a0a;
                --text-color: #ffffff;
                --text-secondary: #a0a0a0;
                --link-bg: #1a1a1a;
                --link-hover: #2a2a2a;
                --border-color: #2a2a2a;
                --shadow: rgba(255, 255, 255, 0.1);
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 680px;
            width: 100%;
            margin: 0 auto;
        }

        .profile {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .profile-bio {
            font-size: 15px;
            color: var(--text-secondary);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .links {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .link-item {
            display: block;
            padding: 20px 24px;
            background-color: var(--link-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .link-item:hover {
            background-color: var(--link-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .link-item:active {
            transform: translateY(0);
        }

        .link-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 500;
        }

        .link-icon {
            font-size: 20px;
            line-height: 1;
        }

        .link-title {
            line-height: 1.4;
        }

        .no-links {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
            font-size: 15px;
        }

        @media (max-width: 480px) {
            body {
                padding: 30px 16px;
            }

            .profile-name {
                font-size: 20px;
            }

            .profile-bio {
                font-size: 14px;
            }

            .link-item {
                padding: 18px 20px;
            }

            .link-content {
                font-size: 15px;
            }
        }

        /* Smooth page load animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            animation: fadeIn 0.5s ease;
        }

        .link-item {
            animation: fadeIn 0.5s ease backwards;
        }

        .link-item:nth-child(1) { animation-delay: 0.1s; }
        .link-item:nth-child(2) { animation-delay: 0.15s; }
        .link-item:nth-child(3) { animation-delay: 0.2s; }
        .link-item:nth-child(4) { animation-delay: 0.25s; }
        .link-item:nth-child(5) { animation-delay: 0.3s; }
        .link-item:nth-child(6) { animation-delay: 0.35s; }
        .link-item:nth-child(7) { animation-delay: 0.4s; }
        .link-item:nth-child(8) { animation-delay: 0.45s; }
        .link-item:nth-child(n+9) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile">
            <h1 class="profile-name"><?php echo esc_html($profile_name); ?></h1>
            <?php if (!empty($profile_bio)): ?>
                <p class="profile-bio"><?php echo esc_html($profile_bio); ?></p>
            <?php endif; ?>
        </div>

        <div class="links">
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" 
                       class="link-item" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <div class="link-content">
                            <?php if (!empty($link['icon'])): ?>
                                <span class="link-icon"><?php echo esc_html($link['icon']); ?></span>
                            <?php endif; ?>
                            <span class="link-title"><?php echo esc_html($link['title']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-links">
                    <p>No links available yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
