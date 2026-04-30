<?php
$banner_text = get_option('smart_banner_text', '');
if (empty($banner_text)) {
    $banner_text = 'Usamos cookies para mejorar la experiencia.';
}

// Leer los links guardados (JSON array de {label, url})
$links_raw = get_option('smart_banner_links', '');
$links     = [];
if (!empty($links_raw)) {
    $decoded = json_decode($links_raw, true);
    if (is_array($decoded)) {
        $links = $decoded;
    }
}
?>
<div id="scp-consent-banner">
    <div class="scp-banner-inner">
        <div class="scp-banner-content">
            <p class="scp-banner-title">Privacidad y cookies</p>
            <p class="scp-banner-message"><?php echo wp_kses_post($banner_text); ?></p>
            <?php if (!empty($links)) : ?>
            <div class="scp-banner-links">
                <?php foreach ($links as $link) :
                    if (empty($link['url']) || empty($link['label'])) continue;
                ?>
                <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="scp-banner-link">
                    <?php echo esc_html($link['label']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="scp-banner-actions">
            <button id="accept-cookies" class="scp-btn scp-btn-accept">Aceptar</button>
            <button id="reject-cookies" class="scp-btn scp-btn-reject">Rechazar</button>
        </div>
    </div>
</div>
