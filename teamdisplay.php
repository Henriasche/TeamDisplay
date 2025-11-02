<?php
/**
 * Plugin Name: TeamDisplay
 * Description: Zeigt Vorstellungstexte der Benutzer mit Vue.js an.
 * Version: 1.0.0
 * Author: Du
 */

defined('ABSPATH') || exit;

/* --- Vue App einbinden --- */
add_action('wp_enqueue_scripts', function () {
    // wp_enqueue_script(
    //     'teamdisplay-script',
    //     plugins_url('dist/app.js', __FILE__),
    //     [],
    //     '1.0',
    //     true
    // );
    wp_enqueue_script(
            'teamdisplay-dev',
            'http://localhost:5173/src/main.ts',
            [],
            null,
            true
        );
    
    // Pass WordPress API URL to the Vue app
    wp_localize_script('teamdisplay-dev', 'teamdisplayConfig', [
        'apiUrl' => rest_url('teamdisplay/v1/intros'),
        'siteName' => get_bloginfo('name')
    ]);
    
    wp_enqueue_style(
        'teamdisplay-style',
        plugins_url('dist/app.css', __FILE__)
    );
});

// Add type="module" attribute to the Vite dev script
add_filter('script_loader_tag', function($tag, $handle, $src) {
    if ($handle === 'teamdisplay-dev') {
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}, 10, 3);

/* --- Benutzerfelder im Profil --- */
function teamdisplay_user_fields($user) {
    $periods = get_user_meta($user->ID, 'teamdisplay_periods', true);
    if (!is_array($periods)) $periods = [];
    $status = get_user_meta($user->ID, 'teamdisplay_status', true);


    ?>
    <h3><?php echo get_bloginfo('name'); ?> und Du</h3>
    <p>Hier kannst du Angaben machen, die auf der Teamerseite angezeigt werden.</p>
    <table class="form-table">
        <tr>
            <th><label for="teamdisplay_visible">Einverständnis</label></th>
            <td>
                <input type="checkbox" name="teamdisplay_visible" id="teamdisplay_visible" value="1"
                    <?php 
                    $visible = get_user_meta($user->ID, 'teamdisplay_visible', true);
                    checked($visible !== '' ? $visible : 1, 1); 
                    ?>>
                <label for="teamdisplay_visible">Ich möchte auf der Teamerseite angezeigt werden</label>
            </td>
        </tr>
        <tr>
            <th><label for="teamdisplay_intro">Ich mache mit, weil ...</label></th>
            <td>
                <textarea name="teamdisplay_intro" id="teamdisplay_intro" rows="5" cols="30"><?php
                    echo esc_textarea(get_user_meta($user->ID, 'teamdisplay_intro', true));
                ?></textarea>
            </td>
        </tr>
       <tr>
            <th><label for="teamdisplay_status">Mitgliedsstatus</label></th>
            <td>
                <select name="teamdisplay_status" id="teamdisplay_status">
                    <option value="aktiv" <?php selected($status, 'aktiv'); ?>>Aktiv</option>
                    <option value="passiv" <?php selected($status, 'passiv'); ?>>Passiv</option>
                    <option value="alumni" <?php selected($status, 'alumni'); ?>>Alumni</option>
                </select>
                <p id="status-description" style="margin-top: 8px; color: #666; font-style: italic;"></p>
            </td>
        </tr>
        <tr>
            <th><label>Aktivitätszeiträume</label></th>
            <td id="teamdisplay-periods">
                <?php foreach ($periods as $i => $p): ?>
                    <div class="period" style="margin-bottom:8px;">
                        <input type="month" name="teamdisplay_periods[<?php echo $i; ?>][from]" value="<?php echo esc_attr($p['from']); ?>">
                        –
                        <input type="month" name="teamdisplay_periods[<?php echo $i; ?>][to]" value="<?php echo esc_attr($p['to']); ?>">
                        <br>
                        <input type="text" name="teamdisplay_periods[<?php echo $i; ?>][activity]" value="<?php echo esc_attr($p['activity'] ?? ''); ?>" placeholder="Beschreibung der Aktivität" style="width:80%; margin-top:4px;">
                        <button type="button" class="remove-period">Entfernen</button>
                    </div>
                <?php endforeach; ?>
                <button type="button" id="add-period">+ Zeitraum hinzufügen</button>
            </td>
        </tr>
    </table>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('teamdisplay-periods');
        const addBtn = document.getElementById('add-period');

        addBtn.addEventListener('click', () => {
            const index = container.querySelectorAll('.period').length;
            const div = document.createElement('div');
            div.classList.add('period');
            div.style.marginBottom = '8px';
            div.innerHTML = `
                <input type="month" name="teamdisplay_periods[${index}][from]" /> –
                <input type="month" name="teamdisplay_periods[${index}][to]" /><br>
                <input type="text" name="teamdisplay_periods[${index}][activity]" placeholder="Beschreibung der Aktivität" style="width:80%; margin-top:4px;">
                <button type="button" class="remove-period">Entfernen</button>
            `;
            container.insertBefore(div, addBtn);
        });

        container.addEventListener('click', e => {
            if (e.target.classList.contains('remove-period')) {
                e.target.parentElement.remove();
            }
        });

        // Status description functionality
        const statusSelect = document.getElementById('teamdisplay_status');
        const statusDescription = document.getElementById('status-description');
        
        const descriptions = {
            'aktiv': 'Für aktive Mitglieder. Du wirst auf der Teamerseite angezeigt.',
            'passiv': 'Für passive Mitglieder. Du wirst nicht auf der Teamerseite angezeigt.',
            'alumni': 'Für ehemalige Mitglieder. Du wirst auf der Teamerseite in einer eigenen Liste angezeigt.'
        };

        function updateDescription() {
            const selectedStatus = statusSelect.value;
            statusDescription.textContent = descriptions[selectedStatus] || '';
        }

        statusSelect.addEventListener('change', updateDescription);
        updateDescription(); // Set initial description
    });
    </script>
<?php }
add_action('show_user_profile', 'teamdisplay_user_fields');
add_action('edit_user_profile', 'teamdisplay_user_fields');

function teamdisplay_save_user_fields($user_id) {
     if (!current_user_can('edit_user', $user_id)) return;

    update_user_meta($user_id, 'teamdisplay_intro', sanitize_textarea_field($_POST['teamdisplay_intro']));
    update_user_meta($user_id, 'teamdisplay_visible', isset($_POST['teamdisplay_visible']) ? 1 : 0);

    if (isset($_POST['teamdisplay_status'])) {
        $valid_statuses = ['anwärter', 'aktiv', 'passiv', 'alumni'];
        $status = in_array($_POST['teamdisplay_status'], $valid_statuses, true)
            ? $_POST['teamdisplay_status']
            : 'aktiv';
        update_user_meta($user_id, 'teamdisplay_status', $status);
    }

    if (isset($_POST['teamdisplay_periods']) && is_array($_POST['teamdisplay_periods'])) {
        $clean = [];
        foreach ($_POST['teamdisplay_periods'] as $p) {
            $from = isset($p['from']) ? sanitize_text_field($p['from']) : '';
            $to   = isset($p['to']) ? sanitize_text_field($p['to']) : '';
            $activity = isset($p['activity']) ? sanitize_text_field($p['activity']) : '';
            if ($from || $to || $activity) {
                $clean[] = ['from' => $from, 'to' => $to, 'activity' => $activity];
            }
        }
        update_user_meta($user_id, 'teamdisplay_periods', $clean);
    } else {
        delete_user_meta($user_id, 'teamdisplay_periods');
    }
}

add_action('personal_options_update', 'teamdisplay_save_user_fields');
add_action('edit_user_profile_update', 'teamdisplay_save_user_fields');

/* --- REST API Endpoint --- */
add_action('rest_api_init', function () {
    register_rest_route('teamdisplay/v1', '/intros', [
        'methods' => 'GET',
        'callback' => function () {
            $users = get_users([
                'meta_key'   => 'teamdisplay_visible',
                'meta_value' => 1
            ]);

            $data = [];
            foreach ($users as $user) {
                $intro = get_user_meta($user->ID, 'teamdisplay_intro', true);
                $status = get_user_meta($user->ID, 'teamdisplay_status', true);
                if ($status === 'passiv') continue;
                $data[] = [
                    'id'       => $user->ID,
                    'name'     => $user->display_name,
                    'avatar'   => get_avatar_url($user->ID, ['size' => 300]),
                    'intro'    => $intro,
                    'status'   => get_user_meta($user->ID, 'teamdisplay_status', true),
                    'periods'  => get_user_meta($user->ID, 'teamdisplay_periods', true),
                ];
            }
            return rest_ensure_response($data);
        }
    ]);
});

/* --- Shortcode für Vue-Mount --- */
function teamdisplay_shortcode() {
    return '<div id="teamdisplay-app"></div>';
}
add_shortcode('teamdisplay_vue', 'teamdisplay_shortcode');

/* --- Seite automatisch anlegen --- */
register_activation_hook(__FILE__, function () {
    $page_title = 'Teamdisplay';
    $page_check = get_page_by_title($page_title);
    if (!$page_check) {
        wp_insert_post([
            'post_title'   => $page_title,
            'post_content' => '[teamdisplay_vue]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
    
    // Set default visibility to 1 for all existing users who don't have it set
    $users = get_users();
    foreach ($users as $user) {
        $visible = get_user_meta($user->ID, 'teamdisplay_visible', true);
        if ($visible === '') {
            update_user_meta($user->ID, 'teamdisplay_visible', 1);
        }
    }
});

