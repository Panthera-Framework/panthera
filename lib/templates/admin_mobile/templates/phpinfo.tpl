    <nav class="tab-fixed">
          <ul class="tab-inner">
            <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
            <li class="active"><a data-ignore="true">{function="localize('Php info')"}</a></li>
          </ul>
    </nav>

    <div class="content inset">
        <div style="text-align: center;">
        <iframe src="?display=phpinfo&cat=admin&action=iframe&_bypass_x_requested_with=True" style="width: 100%; height: 100%; border: 0px;"></iframe>
        </div>
    </div>