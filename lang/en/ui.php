<?php

return [
    'locale' => [
        'label' => 'Language',
    ],

    'resources' => [
        'academic_section' => [
            'singular' => 'Academic section',
            'plural' => 'Academic sections',
            'navigation' => 'Academic Sections',
        ],
        'subject' => [
            'singular' => 'Subject',
            'plural' => 'Subjects',
            'navigation' => 'Subjects',
        ],
        'teaching_assignment' => [
            'singular' => 'Teaching assignment',
            'plural' => 'Teaching assignments',
            'navigation' => 'Teaching Assignments',
        ],
        'schedule' => [
            'singular' => 'Schedule',
            'plural' => 'Schedules',
            'navigation' => 'Schedules',
        ],
        'assignment' => [
            'singular' => 'Assignment',
            'plural' => 'Assignments',
            'navigation' => 'Assignments',
        ],
        'user' => [
            'singular' => 'User',
            'plural' => 'Users',
            'navigation' => 'Users',
        ],
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'password' => 'Password',
        'password_confirmation' => 'Password confirmation',
        'identity_card' => 'Identity card',
        'phone' => 'Phone number',
        'email_verified' => 'Email verified',
        'school_year' => 'School year',
        'code' => 'Code',
        'is_active' => 'Active',
        'academic_section' => 'Section',
        'subject' => 'Subject',
        'teacher' => 'Teacher',
        'teaching_assignment' => 'Teaching assignment',
        'weekday' => 'Weekday',
        'start_time' => 'Start time',
        'end_time' => 'End time',
        'title' => 'Title',
        'description' => 'Description',
        'due_date' => 'Due date',
        'published_at' => 'Published at',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ],

    'weekdays' => [
        'lunes' => 'Monday',
        'martes' => 'Tuesday',
        'miercoles' => 'Wednesday',
        'jueves' => 'Thursday',
        'viernes' => 'Friday',
    ],

    'teaching_assignment_option' => [
        'none_section' => 'No section',
        'none_subject' => 'No subject',
        'none_teacher' => 'No teacher',
        'format' => 'Section: :section | Subject: :subject | Teacher: :teacher',
    ],

    'roles' => [
        'admin' => 'Admin',
        'teacher' => 'Teacher',
        'student' => 'Student',
    ],
];
