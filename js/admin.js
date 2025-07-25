document.addEventListener('DOMContentLoaded', () => {

  // Check if the code editor settings is  available.
  if (typeof wp?.codeEditor?.initialize !== 'function' || typeof kntntEditorSettings === 'undefined') {
    return
  }

  // Check if the textarea element are available.
  const editorTextarea = document.getElementById('kntnt-css-editor')
  if (!editorTextarea) {
    return
  }

  // Initialize the CodeMirror editor.
  const editor = wp.codeEditor.initialize(editorTextarea, kntntEditorSettings.codeEditor)

  // Refresh editor on window resize to prevent layout issues.
  window.addEventListener('resize', () => {
    if (editor && typeof editor.codemirror?.refresh === 'function') {
      editor.codemirror.refresh()
    }
  })

})