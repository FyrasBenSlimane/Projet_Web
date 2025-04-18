<?php
// Users/Freelancers data
$users = [
    [
        'id' => 1,
        'name' => 'Alex Mitchell',
        'email' => 'alex.mitchell@example.com',
        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'level' => 'Level 2 Seller',
        'country' => 'United States',
        'memberSince' => 'Jan 2022',
        'languages' => ['English', 'Spanish'],
        'completedProjects' => 156,
        'rating' => 4.9,
        'ratingCount' => 203,
        'description' => 'Full-stack web developer with over 5 years of experience specializing in creating modern, responsive websites.',
        'skills' => ['HTML5', 'CSS3', 'JavaScript', 'React', 'Node.js', 'PHP']
    ],
    [
        'id' => 2,
        'name' => 'Emma Davis',
        'email' => 'emma.davis@example.com',
        'avatar' => 'https://randomuser.me/api/portraits/women/28.jpg',
        'level' => 'Top Rated Plus',
        'country' => 'United Kingdom',
        'memberSince' => 'Mar 2021',
        'languages' => ['English', 'French'],
        'completedProjects' => 243,
        'rating' => 4.9,
        'ratingCount' => 256,
        'description' => 'UI/UX designer passionate about creating beautiful and functional user experiences.',
        'skills' => ['UI Design', 'UX Design', 'Figma', 'Adobe XD', 'Sketch']
    ]
];

// Gigs data
$gigs = [
    [
        'id' => 1,
        'title' => 'Professional Website Development with Modern Technologies',
        'description' => 'I will create a professional, responsive website using modern technologies like HTML5, CSS3, and JavaScript. The website will be fully optimized for all devices and will follow the latest web standards.',
        'price' => 299,
        'rating' => 4.9,
        'ratingCount' => 128,
        'image' => 'https://images.unsplash.com/photo-1587440871875-191322ee64b0',
        'featured' => true,
        'deliveryTime' => 7,
        'category' => 'web',
        'freelancer' => $users[0]
    ],
    [
        'id' => 2,
        'title' => 'Modern UI/UX Design for Web and Mobile Applications',
        'description' => 'I will design a modern, user-friendly interface for your web or mobile application. The design will be pixel-perfect and follow the latest design trends and best practices.',
        'price' => 199,
        'rating' => 4.9,
        'ratingCount' => 256,
        'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5',
        'featured' => false,
        'deliveryTime' => 5,
        'category' => 'design',
        'freelancer' => $users[1]
    ],
    [
        'id' => 3,
        'title' => 'Complete Digital Marketing Strategy and Implementation',
        'description' => 'I will create and implement a comprehensive digital marketing strategy for your business, including SEO, social media, and content marketing.',
        'price' => 399,
        'rating' => 4.8,
        'ratingCount' => 93,
        'image' => 'https://images.unsplash.com/photo-1542744173-05336fcc7ad4',
        'featured' => true,
        'deliveryTime' => 14,
        'category' => 'marketing',
        'freelancer' => $users[0]
    ]
];

// Categories data
$categories = [
    ['id' => 'web', 'name' => 'Web Development'],
    ['id' => 'design', 'name' => 'Design & Creative'],
    ['id' => 'marketing', 'name' => 'Digital Marketing'],
    ['id' => 'writing', 'name' => 'Writing & Translation'],
    ['id' => 'video', 'name' => 'Video & Animation'],
    ['id' => 'music', 'name' => 'Music & Audio'],
    ['id' => 'programming', 'name' => 'Programming & Tech'],
    ['id' => 'business', 'name' => 'Business']
];
?>