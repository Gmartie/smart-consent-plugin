<?php
$banner_text = get_option('smart_banner_text', '');
if (empty($banner_text)) {
    $banner_text = 'Usamos cookies para mejorar la experiencia.';
}

$links_raw = get_option('smart_banner_links', '');
$links     = [];
if (!empty($links_raw)) {
    $decoded = json_decode($links_raw, true);
    if (is_array($decoded)) {
        $links = $decoded;
    }
}

$trigger_position = get_option('smart_trigger_position', 'left');
$trigger_pos_class = ($trigger_position === 'right') ? 'scp-trigger--right' : 'scp-trigger--left';
?>

<!-- Botón flotante de galleta -->
<button id="scp-cookie-trigger" class="scp-cookie-trigger <?php echo esc_attr($trigger_pos_class); ?>" aria-label="Gestionar preferencias de cookies" title="Gestionar cookies">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="28" height="28" fill="currentColor" aria-hidden="true">
        <!-- Galleta base -->
        <circle cx="32" cy="32" r="30"/>
        <!-- Chips de chocolate (círculos oscuros encima) -->
        <circle cx="22" cy="22" r="4" fill="rgba(0,0,0,0.25)"/>
        <circle cx="38" cy="18" r="3" fill="rgba(0,0,0,0.25)"/>
        <circle cx="44" cy="32" r="4" fill="rgba(0,0,0,0.25)"/>
        <circle cx="28" cy="38" r="3.5" fill="rgba(0,0,0,0.25)"/>
        <circle cx="18" cy="40" r="3" fill="rgba(0,0,0,0.25)"/>
        <circle cx="40" cy="46" r="3" fill="rgba(0,0,0,0.25)"/>
        <!-- Mordisco (recorte simulado con arco blanco) -->
        <path d="M54 18 Q64 8 58 2 Q52 10 44 12 Q46 20 54 18Z" fill="white"/>
    </svg>
</button>

<!-- Banner de consentimiento (oculto hasta que se abre) -->
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
