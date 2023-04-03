<div class="opa-show-dashboard__options small">
    <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $show_id, 'section' => 'awards' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--awards">
        <?php _e( 'Awards', OPA_DOMAIN ) ?>
    </a>
    <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $show_id, 'section' => 'artists' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--artists">
        <?php _e( 'Artists', OPA_DOMAIN ) ?>
    </a>
    <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $show_id, 'section' => 'artwork' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--artwork">
        <?php _e( 'Artwork', OPA_DOMAIN ) ?>
    </a>
    <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $show_id, 'section' => 'jurors' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--jurors">
        <?php _e( 'Jurors', OPA_DOMAIN ) ?>
    </a>
    <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $show_id, 'section' => 'jury-rounds' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--jury-rounds">
        <?php _e( 'Jury Rounds', OPA_DOMAIN ) ?>
    </a>
    <?php /*
        <a href="<?php echo OPA_Menu::helper_url( array( 'show_id' => $_GET['show_id'], 'section' => 'reports' ) ) ?>" class="opa-show-dashboard__option opa-show-dashboard__option--reports">
            <?php _e( 'Reports', OPA_DOMAIN ) ?>
        </a>
 */ ?>
</div>