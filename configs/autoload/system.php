<?php

return [
    'app' => [
        'name' => 'GKTH',
        'made' => 2023,
        'icon' => '/assets/images/favicon.png',
        'logo' => '/assets/images/favicon.png',
        'default-title' => 'Góc khuất tâm hồn',
        'description' => 'Tinh tế và sâu sắc, trang web cùng bạn khám phá những khía cạnh tinh túy của tâm hồn con người từ những cảm xúc đa dạng đến những suy tư sâu xa. Nơi đây, mọi người có cơ hội thấu hiểu bản thân mình qua từng góc nhìn độc đáo và chân thực, tạo nên một không gian tưởng tượng và cảm xúc sâu lắng.',
        'theme-color' => '#22292F',
        'google-site-verification' => 'XZSx4SX3UMbXV6t4HXrC6qk0gM96MVMUOt1EasWssW0'
    ],
    'tokenlogin' => md5(sha1(ADMIN_LOGIN['account']).ADMIN_LOGIN['password'])
];
