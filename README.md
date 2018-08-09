# Readme

## View System

The correct syntaxes:
`{{aVariable}}`
`{{0a_Variable}}`
`{{_aVariAble_}}`

Incorrect:
`{{a Variable}}`
`{{ aVariable}}`
`{{aVariable} }`

In fact, there is always 2 symbols surrounding the variables. No spaces are allowed

1. Replace foreach
2. Replace if
3. Replace variables
4. Translate
5. Replace Links
6. Include routes

### Foreach

```
$data['dataIndexName'] = array();
$data['dataIndexName'][]['aVariableInTheForeach'] = 'First element';
$data['dataIndexName'][]['aVariableInTheForeach'] = 'Last element';
```

```
[~dataIndexName~]
  <something>{~aVariableInTheForeach~}</something>
[~~]
```

Result:
```
<something>First element</something>
<something>Last element</something>
```

### If

```
{?dataBoolean?}
  <something>{{variable}} or anything else</something>
{??}
```
Result: `<something>{{variable}} or anything else</something>` will be displayed only if `$data['dataBoolean'] == true`

### Variables

```<something>{{variable}} or anything else</something>```

Result:

With `data['variable'] = 'My content';`, the result will be `<something>My content or anything else</something>`

### Translation

`<something>{>stringId<} or {>anything<}</something>`

In this case, stringId and anything must be set in the str file (`plugins/plugin/view/strings/language.str`)

For example, a correct `.str` file is

```
stringId: This
anything: that
something_else: Something not used yet
```

Result:
`<something>This or that</something>`

### Links

*It's currently not implemented*

It simply replace a route with the correct link. This is way simpler that way to change the server way of naming

### Routes

This system allow to 'include' the result of a route into an other html file.
A route mustn't be included in itself.

`{{errors/404}}` will call the corresponding controller and generate the corresponding html

