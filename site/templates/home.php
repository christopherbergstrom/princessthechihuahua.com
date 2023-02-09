<?php namespace ProcessWire;

// Template file for “home” template used by the homepage
  include "_head.php";
  $wishes = $pages->find("template=basic-page");
?>
  <div class="uk-section uk-section-small uk-padding-remove-bottom" uk-scrollspy="cls: uk-animation-fade; delay: 250;">
    <div class="uk-container">
      <div class="video panel uk-padding">
      <iframe width="100%" height="100%" src="https://www.youtube.com/embed/6b83vWX4Lgg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
      </div>
    </div>
  </div>
  <div class="uk-section uk-section-small uk-padding-remove-bottom">
    <div class="uk-container">
      <div class="uk-padding" uk-grid>
        <?php foreach($wishes as $wish): ?>
        <div class="uk-width-1-2@m">
          <div class="panel uk-padding-remove uk-card uk-card-default" uk-scrollspy="cls: uk-animation-fade; delay: 250;">
            <div class="uk-card-body">
              <?= $wish->message ?>
            </div>
            <div class="uk-card-footer">
              <?= $wish->author ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="uk-section uk-section-small" uk-scrollspy="cls: uk-animation-fade; delay: 250;">
    <div class="uk-container">
      <div class="panel uk-padding">
        <?php echo $form; ?>
      </div>
    </div>
  </div>
  </main>
</body>
</html>
