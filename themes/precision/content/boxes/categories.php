<div id="categories">
  <h2>Categories</h2>
  <?php $tree = $this->container->get('categoryService')->getCategoryTree($session->getLanguageId()); ?>
  <?php echo $macro->categoryTree($tree, true, ZMSettings::get('isUseCategoryPage')) ?>
</div>
