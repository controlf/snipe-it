<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Labels</title>

  </head>
  <body>

    <?php
      $settings->labels_width = $settings->labels_width - $settings->labels_display_sgutter;
      $settings->labels_height = $settings->labels_height - $settings->labels_display_bgutter;
      // Simplify checks
      $bc_enabled = ($settings->alt_barcode_enabled=='1') && ($settings->alt_barcode!='');
      // Leave space on bottom for 1D barcode if necessary
      //$qr_size = $bc_enabled ? $settings->labels_height - $settings->labels_display_sgutter*2 : $settings->labels_height - $settings->labels_display_sgutter*2 - 0.3;
      $qr_size = $settings->labels_height - $settings->labels_display_bgutter*2;
      // Leave space on left for QR code if necessary
      $qr_txt_size   = ($settings->qr_code=='1' ? $settings->labels_width - $qr_size - .1: $settings->labels_width);
      //$qr_txt_height = ($bc_enabled ? $qr_size - 0.3 : $qr_size);
    ?>

    <style>

      div.qr_img {
        width: {{ $qr_size }}in;
        height: {{ $qr_size }}in;
        display: block;
        /*padding-right: .04in;*/
      }
      img.qr_img {
        /*width: {{ $qr_size }}in;
        height: {{ $qr_size }}in;*/
        /*Temporary fix for the QR code having a margin...
          Image is 99px wide/tall, but the QR code itself is only 87px tall/wide.*/
        width: 113.79%;
        height: 113.79%;
        margin-top: -6.9%;
        margin-left: -6.9%;
      }
      img.barcode {
        display: block;
        /*height: 0.15in;*/
	/*80% scaling and auto centering makes this easier to scan*/
        width: 80%;
        margin-left: auto;
        margin-right: auto;
      }

      .qr_text {
        width: 100%;
        height: auto;
        padding-top: {{ $settings->labels_display_bgutter }}in;
        font-family: arial, helvetica, sans-serif;
        /*padding-right: .01in;*/
        overflow: hidden !important;
        display: inline-block;
        word-wrap: break-word;
        word-break: break-word;
	/*Break word to split long model names sensibly*/
      }

      div.barcode_container {
        display: inline;
        width: 100%;
        overflow: hidden;
      }

      @if ($snipeSettings->custom_css)
        {{ $snipeSettings->show_custom_css() }}
      @endif
    </style>

    <style media="screen">
      * {
        box-sizing: border-box;
      }

      body {
        background-color: #CCC;
        margin: 0;
        padding: 0;
        text-align: center;
      }

      #label-canvas {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        align-content: center;
      }

      .page {
        display: inline-flex;
        counter-increment: label-page;
        margin: 0.2in 0.1in 0.0in 0.1in;
        box-shadow: 0.0in 0.1in 0.2in rgba(0,0,0,0.5);
          
      }.page:last-of-type {
        margin-bottom: 0.2in;
      }.page::before {
        content: counter(label-page);
        display: block;
        position: absolute;
        font-family: sans-serif;
        font-weight: bold;
        margin: -0.6em;
        padding: 0.1em;
        width: 1.2em;
        height: 1.2em;
        font-size: 8pt;
        background-color: rgba(0,0,0,0.2);
        border-radius: 100%;
        color: rgba(0,0,0,0.4);
      }

      .label {
        border: 1px dotted #888;
      }
    </style>

    <style media="print">
      * {
        box-sizing: border-box;
      }

      body {
        background-color: #FFF;
        margin: 0;
        padding: 0;
        text-align: center;
      }

      #label-canvas {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        align-content: center;
      }

      .page {
        display: inline-flex;
        page-break-after: always;
        margin: 0;
      }.page:last-of-type {
        /*margin-bottom: 0.2in;*/
        page-break-after: avoid;
      }
    </style>

    <style>
      @page {
        size: {{ $settings->labels_pagewidth }}in {{ $settings->labels_pageheight }}in;
        margin: 0;
      }

      body {
        font-family: arial, helvetica, sans-serif;
        font-size: {{ $settings->labels_fontsize }}pt;
      }

      .page {
        width: {{ $settings->labels_pagewidth }}in;
        height: {{ $settings->labels_pageheight }}in;
        background-color: white;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-content: flex-start;
        /*grid-template-columns: fit-content(100%);*/
      }

      .label {
        width: {{ $settings->labels_width }}in;
        height: {{ $settings->labels_height }}in;
        margin: {{ $settings->labels_pmargin_top }}in
                {{ $settings->labels_pmargin_right }}in
                {{ $settings->labels_pmargin_bottom }}in
                {{ $settings->labels_pmargin_left }}in;
        display: grid;
        grid-template-rows: auto 20%;
        grid-template-columns: auto 0.05in auto;
      }

      .qr_img {
        grid-column: 1;
        grid-row: 1 / 3;
      }

      .qr_text {
        grid-column: 3;
        grid-row: 1 / 2;
      }

      .barcode_container {
        grid-column: 3;
        grid-row: 2 / 3;
      }
    </style>

    <div id="label-canvas">
      @for ($pcount = 0; $pcount < ceil(count($assets)/$settings->labels_per_page); $pcount++)
        <div class="page">
          @for ($lcount = $pcount * $settings->labels_per_page; ($lcount - $pcount * $settings->labels_per_page) < $settings->labels_per_page; $lcount++)
            @if ($lcount >= count($assets))
              @break
            @endif
            <?php $asset = $assets[$lcount]; ?>

            <div class="label"> 
              @if ($settings->qr_code=='1')
                <div class="qr_img">
                  <img src="./{{ $asset->id }}/qr_code" class="qr_img">
                </div>
              @endif

              <div class="qr_text">
                @if ($settings->qr_text!='')
                  <div class="pull-left">
                    <strong>{{ $settings->qr_text }}</strong>
                    <br>
                  </div>
                @endif
                @if (($settings->labels_display_company_name=='1') && ($asset->company))
                  <div class="pull-left">
                    <strong>Company:</strong> {{ $asset->company->name }}
                  </div>
                @endif
                @if (($settings->labels_display_name=='1') && ($asset->name!=''))
                  <div class="pull-left">
                    <strong>Name:</strong> {{ $asset->name }}
                  </div>
                @endif
                @if (($settings->labels_display_tag=='1') && ($asset->asset_tag!=''))
		  {{-- Displays asset tag and legacy ID if available --}}
                  <div class="pull-left">
                    <strong>Tag:</strong> {{ $asset->asset_tag }}
                      @if ($asset->_snipeit_legacy_id_3!='')
                        <strong>Legacy:</strong> {{ $asset->_snipeit_legacy_id_3 }}
                      @endif
                  </div>
                @endif
                @if (($settings->labels_display_serial=='1') && ($asset->serial!=''))
                  <div class="pull-left">
                    <strong>Serial:</strong> {{ $asset->serial }}
                  </div>
                @endif
                @if (($settings->labels_display_model=='1') && ($asset->model->name!=''))
                  <div class="pull-left">
                    <strong>Model:</strong> {{ $asset->model->manufacturer->name }} {{ $asset->model->name }} {{ $asset->model->model_number }}
                  </div>
                @endif
		@if ($asset->_snipeit_course_10!='')
                  <div class="pull-left">
                    <strong>Course:</strong> {{ $asset->_snipeit_course_10 }}
                  </div>
                @endif
              </div>

              @if ((($settings->alt_barcode_enabled=='1') && $settings->alt_barcode!=''))
                <div class="barcode_container">
                  <img src="./{{ $asset->id }}/barcode" class="barcode">
                </div>
              @endif

            </div>
          @endfor
        </div>
      @endfor
    </div>
    
  </body>
</html>
