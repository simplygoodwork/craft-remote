import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

let hostEditorWrapper = document.querySelector('#settings-host-editor-wrapper');
if (hostEditorWrapper) {
  // let's init our own autocompletions for JSON
  let endpointUrl = hostEditorWrapper.getAttribute('data-autocomplete-endpoint');
  let codeEditorOptions = JSON.parse(hostEditorWrapper.getAttribute('data-code-editor-options'));
  let allowedHostKeys = JSON.parse(hostEditorWrapper.getAttribute('data-allowed-host-keys'));
//     fetch endpointUrl
  fetch(endpointUrl + '&' + new URLSearchParams({
    'fieldType': 'HostField',
    'codeEditorOptions': codeEditorOptions
  }))
    .then(response =>
      response.json()
    )
    .then(data => {
      if (typeof window.monacoAutocompleteItems === 'undefined') {
        window.monacoAutocompleteItems = {};
      }
      const completionItems = data;
      // window.monacoEditorInstance['settings-host-code-editor']

      for (const [name, autocomplete] of Object.entries(completionItems)) {
        if (!(autocomplete.name in window.monacoAutocompleteItems)) {
          window.monacoAutocompleteItems[autocomplete.name] = autocomplete.name;

          addCompletionItemsToMonaco(autocomplete.__completions, autocomplete.type, autocomplete.hasSubProperties);
          // addHoverHandlerToMonaco(autocomplete.__completions, autocomplete.type);
        }
      }
      window.monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
          validate: true,
          schemas: [],
          enableSchemaRequest: false,
          allowComments: true,
          trailingCommas: 'ignore',
          comments: 'ignore'
      });
      validateHostJSON(window.monacoEditorInstances['settings-host-code-editor'].getModel(), allowedHostKeys)
    })
    .catch((error) => {
      console.error('Error:', error);
    });

  if (document.readyState === 'complete') {
    initHostValidation()
  } else {
    window.addEventListener('load', function () {
      initHostValidation()
    })
  }

  function initHostValidation() {
    console.log('window loaded')
    let editor = window.monacoEditorInstances['settings-host-code-editor'];

    validateHostJSON(editor.getModel(), allowedHostKeys)

    editor.onDidChangeModelContent(function () {
      console.log('editor content changed')
      validateHostJSON(editor.getModel(), allowedHostKeys)
    })

    editor.onDidBlurEditorWidget(function () {
      console.log('editor blurred')
      validateHostJSON(editor.getModel(), allowedHostKeys)
    })

    // Listen for Enter key presses to trigger suggestions
    // editor.addCommand(window.monaco.KeyCode.Enter, function () {
    //   editor.trigger('keyboard', 'editor.action.triggerSuggest', {});
    // });

    // Optionally, also listen for new lines to trigger suggestions
    editor.onDidChangeModelContent(function (event) {
      event.changes.forEach(function (change) {
        if (change.text.includes('\n')) {
          editor.trigger('keyboard', 'editor.action.triggerSuggest', {});
        }
      });
    });
  }
}

function addCompletionItemsToMonaco(completionItems, autocompleteType, hasSubProperties) {
  window.monaco.languages.registerCompletionItemProvider('json', {
    triggerCharacters: [' ', ': ', '"'],
    provideCompletionItems: function (model, position, token) {

      const result = [];

      let currentItems = completionItems;

      // Get the last word the user has typed
      const currentLine = model.getValueInRange({
        startLineNumber: position.lineNumber,
        startColumn: 0,
        endLineNumber: position.lineNumber,
        endColumn: position.column
      });

      const triggerCharacter = currentLine.trim().slice(-1);
      const currentWords = currentLine.replace("\t", "").split(" ");
      let currentWord = currentWords[currentWords.length - 1];
      let allKeys = getAllKeys(model);

      // If we're inside of a key, show the possible autocompletes for that specific key.
      if (currentLine.match(/"([^"]*)"\s*:\s*"?$/)) {
        let keyMatch = currentLine.match(/"([^"]*)"\s*:\s*"?$/);
        let key = keyMatch[1];

        if (currentItems[key]) {
          Object.keys(currentItems[key]).forEach((item, index) => {
            if (item !== '__completions') {
              if(triggerCharacter === '"'){
                let modifiedItem = {...currentItems[key][item]['__completions'], insertText: currentItems[key][item]['__completions'].insertText.replace(/[": ]/g, '')}
                result.push(modifiedItem)
              } else {
                result.push(currentItems[key][item]['__completions'])
              }
            }
          })
          return {suggestions: result};
        }
      }


      if (typeof currentItems !== 'undefined') {
        for (const item in currentItems) {
          if (currentItems.hasOwnProperty(item) && !item.startsWith("__")) {
            const completionItem = currentItems[item]["__completions"];
            if (typeof completionItem !== 'undefined') {
              // Monaco adds a 'range' to the object, to denote where the autocomplete is triggered from,
              // which needs to be removed each time the autocomplete objects are re-used
              delete completionItem.range;
              if ('documentation' in completionItem && typeof completionItem.documentation !== 'object') {
                const docs = completionItem.documentation;
                completionItem.documentation = {
                  value: docs,
                  isTrusted: true,
                  supportsHtml: true
                }
              }

              console.log('allKeys', allKeys, 'item', item, allKeys.indexOf(item))

              if(allKeys.indexOf(item) === -1){
                console.log('result', result)
                if(triggerCharacter === '"'){
                  let modifiedItem = {...currentItems[item]["__completions"], insertText: currentItems[item]["__completions"].insertText.replace(/[": ]/g, '')}
                  result.push(modifiedItem)
                } else {
                  result.push(completionItem);
                }
              }
            }
          }
        }
      }

      const finalItems = {
        suggestions: result
      }
      return finalItems;
    }
  });
}

function addHoverHandlerToMonaco(completionItems, autocompleteType) {
  window.monaco.languages.registerHoverProvider('json', {
    provideHover: function (model, position) {
      const currentLine = model.getValueInRange({
        startLineNumber: position.lineNumber,
        startColumn: 0,
        endLineNumber: position.lineNumber,
        endColumn: model.getLineMaxColumn(position.lineNumber)
      });
      const currentWord = model.getWordAtPosition(position);
      if (currentWord === null) {
        return;
      }
      let searchLine = currentLine.substring(0, currentWord.endColumn - 1)
      let isSubProperty = false;
      let currentItems = completionItems;
      for (let i = searchLine.length; i >= 0; i--) {
        if (searchLine[i] === ' ') {
          searchLine = currentLine.substring(i + 1, searchLine.length);
          break;
        }
      }
      if (searchLine.includes('.')) {
        isSubProperty = true;
      }
      if (isSubProperty) {
        // Is a sub-property, get a list of parent properties
        const parents = searchLine.substring(0, searchLine.length).split(".");
        // Loop through all the parents to traverse the completion items and find the current one
        for (let i = 0; i < parents.length - 1; i++) {
          const thisParent = parents[i].replace(/[{(<]/, '');
          if (currentItems.hasOwnProperty(thisParent)) {
            currentItems = currentItems[thisParent];
          } else {
            return;
          }
        }
      }
      if (typeof currentItems !== 'undefined' && typeof currentItems[currentWord.word] !== 'undefined') {
        const completionItem = currentItems[currentWord.word][COMPLETION_KEY];
        if (typeof completionItem !== 'undefined') {
          let docs = completionItem.documentation;
          if (typeof completionItem.documentation === 'object') {
            docs = completionItem.documentation.value;
          }

          const finalHover = {
            range: new window.monaco.Range(position.lineNumber, currentWord.startColumn, position.lineNumber, currentWord.endColumn),
            contents: [
              {value: '**' + completionItem.detail + '**'},
              {value: docs},
            ]
          }
          return finalHover
        }
      }
      return;
    }
  });
}

function validateHostJSON(model, allowedHostKeys) {
  let value = model.getValue();
  let markers = [];
  try {
    let parsed = JSON.parse(value);
    // Iterate through the keys and validate
    Object.keys(parsed).forEach(function (key) {
      if (allowedHostKeys.indexOf(key) === -1) {
        var keyMatch = model.findMatches(`"${key}"`, true, true, false, null, true)[0];
        if (keyMatch) {
          markers.push({
            startLineNumber: keyMatch.range.startLineNumber,
            startColumn: keyMatch.range.startColumn,
            endLineNumber: keyMatch.range.endLineNumber,
            endColumn: keyMatch.range.endColumn,
            message: `'${key}' is not a valid property for the Host settings object`,
            severity: window.monaco.MarkerSeverity.Error
          });
        }
      }
    });
  } catch (e) {
    // console.log(e)
    // Add a marker for JSON parsing errors
    markers.push({
      startLineNumber: 1,
      startColumn: 1,
      endLineNumber: model.getLineCount(),
      endColumn: model.getLineMaxColumn(model.getLineCount()),
      message: 'Invalid JSON',
      severity: window.monaco.MarkerSeverity.Error
    });
  }
  window.monaco.editor.setModelMarkers(model, 'json', markers);
}

// Function to get all keys in the current JSON content
function getAllKeys(model) {
  const value = model.getValue();

  if(window['monacoFieldKeys'] === undefined){
    window['monacoFieldKeys'] = {};
  }

  if(window['monacoFieldKeys'][model.id] === undefined){
    window['monacoFieldKeys'][model.id] = [];
  }

  try {
    const jsonObject = JSON.parse(value);
    window['monacoFieldKeys'][model.id] = Object.keys(jsonObject);
    return window['monacoFieldKeys'][model.id];
  } catch (e) {
    // console.log(e);
    return window['monacoFieldKeys'][model.id];
  }
}
