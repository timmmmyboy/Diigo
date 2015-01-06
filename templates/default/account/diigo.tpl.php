<div class="row">

    <div class="span10 offset1">
        <?=$this->draw('account/menu')?>
        <h1>Diigo</h1>
    </div>

</div>
<div class="row">
    <div class="span10 offset1">
        <form action="/account/diigo/" class="form-horizontal" method="post">
            <div class="control-group">
                <div class="controls-config">
                    <p>
                        To publish bookmarks to Diigo, <a href="https://www.diigo.com/api_keys/new/" target="_blank">get an API key from the Diigo site</a> and enter it below along with your Diigo username and password.</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="name">Username</label>
                <div class="controls">
                    <input type="text" id="name" placeholder="Username" class="span4" name="dgusername" value="<?=htmlspecialchars(\Idno\Core\site()->config()->diigo['dgusername'])?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="name">Password</label>
                <div class="controls">
                    <input type="password" id="name" placeholder="Password" class="span4" name="dgpassword" value="<?=htmlspecialchars(\Idno\Core\site()->config()->diigo['dgpassword'])?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="name">API Key</label>
                <div class="controls">
                    <input type="text" id="name" placeholder="API Key" class="span4" name="dgapiKey" value="<?=htmlspecialchars(\Idno\Core\site()->config()->diigo['dgapiKey'])?>" >
                </div>
            </div>
            <div class="control-group">
                <div class="controls-save">
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </div>
            <?= \Idno\Core\site()->actions()->signForm('/account/diigo/')?>
        </form>
    </div>
</div>
