(function (OBR) {
  const params = new window.URLSearchParams(window.location.search);
  const HOST = params.has('sst_dev') ? 'http://localhost:7100/SST-API' : 'https://sst.outbrain.com/SST-API';
  const EDIT_ENDPOINT = HOST + '/static/edit.js';
  const RECS_ENDPOINT = HOST + '/recs';
  const replacedWidgets = [];

  function injectScript (uri) {
    const search = new window.URLSearchParams(window.location.search);

    if (search.has('sst_edit') && search.get('sst_edit') === 'true') {
      const head = document.getElementsByTagName('head')[0];
      const script = document.createElement('script');
      script.src = uri;
      script.onerror = onReadMode;
      script.onabort = onReadMode;

      head.appendChild(script);
      return;
    }

    onReadMode();
  }

  function onReadMode () {
    OBR.extern.registerOnWidgetRendered(replace);
  }

  function replace (widget) {
    try {
      const widgetIdx = window['OB_PROXY'].getWidgetIndex(widget);
      if (replacedWidgets.indexOf(widgetIdx) < 0) {
        replacedWidgets.push(widgetIdx);

        const search = new window.URLSearchParams(window.location.search);

        if (search.has('sst_token')) {
          const odbUrl = window['OB_PROXY'].generateOdbCall(widgetIdx);
          const endpoint = odbUrl.replace('https://odb.outbrain.com/utils/get', RECS_ENDPOINT) + '&token=' + search.get('sst_token') + '&cache_only=true';
          const options = { headers: { 'Content-type': 'application/json; charset=UTF-8' } };

          window.fetch(endpoint, options).then((response) => {
            response.json().then((result) => {
              if(result.success) {
                const urlParams = new window.URLSearchParams(new URL(odbUrl.html).search);
                OBR.extern.refreshSpecificWidgetWithData(Number(widgetIdx), result.data, urlParams.get('url'));
              }
            });
          });
        }
      }
    } catch (err) {}
  }

  injectScript(EDIT_ENDPOINT);
})(OBR);
