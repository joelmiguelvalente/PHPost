<?php

class ListDefinition extends JBBCode\CodeDefinition {

   private bool $option;

   public function __construct(bool $withOption = false) {
      $this->option = $withOption;
      $this->setTagName('list');
      $this->parseContent = true;
      $this->useOption = $this->option;
      $this->nestLimit = -1;
   }

   public function asHtml(JBBCode\ElementNode $el) {
      $option = $el->getAttribute();
      if($this->option) $option = $option['list'];
      $tag = 'ul';
      $style = '';
      $ordered = ['decimal','lower-alpha','upper-alpha','lower-roman','upper-roman'];

      if ($option && in_array($option, $ordered)) {
         $tag = 'ol';
         $style = "list-style:$option!important;";
      }
      if ($option && in_array($option, ['disc','circle','square'])) {
         $style = "list-style:$option!important;";
      }

      $style = empty($style) ? " style=\"list-style-type:disc;\"" : " style=\"{$style}\"";
      $html = "<{$tag} class=\"bbc-list\"{$style}>";

      foreach ($el->getChildren() as $child) {
          $html .= trim($child->getAsHtml());
      }

      $html .= "</{$tag}>";
      return $html;
    }
}