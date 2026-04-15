<?php

/**
 * @name config/AbstractConfig.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

abstract class AbstractConfig
{
   /**
    * Contenedor interno de configuración
    *
    * @var array<string, mixed>
    */
   protected array $items = [];

   public function __construct()
   {
      $this->loadLocalOverrides();
   }

   /**
    * Carga archivo .local.php si existe y mergea sobre defaults
    */
   protected function loadLocalOverrides(): void
   {
      $reflect = new ReflectionClass($this);
      $baseFile = $reflect->getFileName();
      $localFile = preg_replace('/\.php$/', '.local.php', $baseFile);
      
      if (is_file($localFile)) {
         $local = require $localFile;

         if (!is_array($local)) {
            throw new RuntimeException('Config local inválida');
         }

         $this->items = array_replace_recursive($this->items, $local);
      }
   }

   /**
    * Obtiene un valor de configuración
    */
   public function get(string $key, mixed $default = null): mixed
   {
      if ($key === '') return $this->items;

      $value = $this->items;
      foreach (explode('.', $key) as $segment) {
         if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
         }
         $value = $value[$segment];
      }
      return $value;
   }
}
