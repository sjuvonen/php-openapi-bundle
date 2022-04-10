window.onload = function () {
  const data = JSON.parse(document.getElementById('api-docs-data').innerHTML)

  const ui = SwaggerUIBundle({
    spec: data,
    dom_id: '#swagger-ui',
    validatorUrl: null,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: 'StandaloneLayout'
  })

  window.ui = ui
}
