<h2><?php echo __('ホーム画面レイアウト設定') ?></h2>

<?php echo $form->renderFormTag(url_for('design/homeLayout')) ?>
<p><input type="submit" value="<?php echo __('設定変更') ?>" /></p>
<?php echo $form['layout']->render() ?>
</form>