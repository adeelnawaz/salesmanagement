<?php

class My_View_Helper_SortOrder extends Zend_View_Helper_Abstract {

    public function sortOrder($field, $title) {
        $order = $this->view->sort == $field && $this->view->order == 'DESC' ? '' : ($this->view->sort == $field ? 'DESC' : 'ASC');
        if (!empty($order)) {
            $href = $this->view->url(array('sort' => $field, 'order' => $order));
        } else {
            $href = $this->view->url(array('sort' => NULL, 'order' => NULL));
        }
        ?>
        <a href="<?php echo $href; ?>"><?php echo $title; ?><span class="sort <?php echo ($this->view->sort == $field ? $this->view->order : ''); ?>"></span></a>
        <?php
    }

}
?>
