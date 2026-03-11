<?php

return [
    'locale' => [
        'label' => 'Idioma',
    ],

    'resources' => [
        'academic_section' => [
            'singular' => 'sección académica',
            'plural' => 'secciones académicas',
            'navigation' => 'Secciones Académicas',
        ],
        'subject' => [
            'singular' => 'materia',
            'plural' => 'materias',
            'navigation' => 'Materias',
        ],
        'teaching_assignment' => [
            'singular' => 'asignación docente',
            'plural' => 'asignaciones docentes',
            'navigation' => 'Asignaciones Docentes',
        ],
        'schedule' => [
            'singular' => 'horario',
            'plural' => 'horarios',
            'navigation' => 'Horarios',
        ],
        'assignment' => [
            'singular' => 'tarea',
            'plural' => 'tareas',
            'navigation' => 'Tareas',
        ],
    ],

    'fields' => [
        'name' => 'Nombre',
        'school_year' => 'Año escolar',
        'code' => 'Código',
        'is_active' => 'Activo',
        'academic_section' => 'Sección',
        'subject' => 'Materia',
        'teacher' => 'Docente',
        'teaching_assignment' => 'Asignación docente',
        'weekday' => 'Día',
        'start_time' => 'Hora inicio',
        'end_time' => 'Hora fin',
        'title' => 'Título',
        'description' => 'Descripción',
        'due_date' => 'Fecha límite',
        'published_at' => 'Publicado en',
        'created_at' => 'Creado en',
        'updated_at' => 'Actualizado en',
    ],

    'weekdays' => [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
    ],

    'teaching_assignment_option' => [
        'none_section' => 'Sin sección',
        'none_subject' => 'Sin materia',
        'none_teacher' => 'Sin docente',
        'format' => 'Sección: :section | Materia: :subject | Docente: :teacher',
    ],
];
