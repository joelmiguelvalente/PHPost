<?php

declare(strict_types=1);

abstract class AbstractConfig
{
   /**
    * Contenedor interno de configuración
    *
    * @var array<string, mixed>
    */
   protected array $items = [];

   /**
    * Obtiene un valor de configuración
    */
   public function get(string $key, mixed $default = null): mixed
   {
      if ($key === '') {
         return $this->items;
      }

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