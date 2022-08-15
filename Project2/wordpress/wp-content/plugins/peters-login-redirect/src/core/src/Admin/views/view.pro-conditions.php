<?php
$pro_conditions = [
    'LearnDash'            => [
        esc_html__('Enrolled Course', 'peters-login-redirect'),
        esc_html__('User Group', 'peters-login-redirect')
    ],
    'WooCommerce'          => [
        esc_html__('Purchased Product', 'peters-login-redirect'),
        esc_html__('Purchased Product Category', 'peters-login-redirect'),
        esc_html__('Active User Subscription', 'peters-login-redirect'),
        esc_html__('Active Membership Plan', 'peters-login-redirect')
    ],
    'MemberPress'          => [
        esc_html__('Subscribed Membership', 'peters-login-redirect')
    ],
    'Restrict Content Pro' => [
        esc_html__('Active Membership Level', 'peters-login-redirect')
    ],
    'LifterLMS'            => [
        esc_html__('Enrolled Course', 'peters-login-redirect'),
        esc_html__('Enrolled Membership', 'peters-login-redirect')
    ],
    'TutorLMS'            => [
        esc_html__('Enrolled Course', 'peters-login-redirect')
    ]
];
?>

<div class="ptr-loginwp-pro-conditions-wrap">
    <?php foreach ($pro_conditions as $label => $condition): ?>
        <div class="ptr-loginwp-pro-condition">
            <strong><?= $label ?>:</strong> <?= implode(', ', $condition) ?>.
        </div>
    <?php endforeach; ?>
    <div>
        <a href="https://loginwp.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=pro_conditions_metabox" target="__blank" class="button-primary">
            <?php esc_html_e('Get LoginWP Pro →', 'peters-login-redirect') ?>
        </a>
    </div>
</div>