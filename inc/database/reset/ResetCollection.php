<?php 

final class ResetCollection
{
    public static function definitions(): array
    {
        return [
            'content' => [
                'label' => 'Reset de contenido',
                'danger' => false,
                'tables' => [
                    'p_posts',
                    'p_comentarios',
                    'p_favoritos',
                    'p_votos',
                    'p_borradores',
                    'f_fotos',
                    'f_comentarios',
                    'f_favoritos',
                    'f_votos',
                    'u_muro',
                    'u_muro_comentarios',
                    'u_muro_adjuntos',
                    'u_muro_likes',
                    'u_actividad',
                    'u_monitor',
                    'w_denuncias',
                    'w_historial',
                    'w_visitas',
                    'w_sitemap'
                ]
            ],

            'configs' => [
                'label' => 'Reset de configuraciones',
                'danger' => false,
                'callbacks' => [
                    'resetUserConfigs',
                    'resetStats'
                ]
            ],

            'fresh' => [
                'label' => 'Reinicio tipo instalación nueva',
                'danger' => true,
                'extends' => ['content', 'configs'],
                'tables' => [
                    'w_noticias',
                    'w_medallas',
                    'w_medallas_assign'
                ]
            ]
        ];
    }
}
