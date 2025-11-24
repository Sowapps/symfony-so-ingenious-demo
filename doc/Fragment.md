# Fragment

A fragment is an editable block of HTML.  
It could be standalone a compose any part of a page, or it could be a page.

There is 3 types of fragment:

- The page fragment, the whole generated HTML is a full page with <html> tag.
- The standalone free fragment, a block of HTML usable once and related to another.
- The standalone snippet fragment, a reusable block of HTML that you selected in list of snippets matching the required template.

A fragment can contain :

- Basic values : string, object of string, or even list of object of string. This is available in `properties` of fragment.
- Another fragment, available in `children` of fragment :
    - A standalone child fragment built for this parent
    - A snippet child fragment reusable between multiple parents, identified by template

## Commands

Some commands provide you with useful tools to manipulate fragments.  
Use help to get more information about each command.

##### Fragment Validator

Fragment validator command checks all fragments are matching the requirements of the template.  
This will check, among other things, the properties and the relationships with its children.

**Fragment validator command**

```shell
bin/console app:fragment:validate
```

##### Fragment Rendering

Fragment rendering command is generating HTMl of the template contents.

**Fragment rendering command**

```shell
bin/console app:fragment:render <id>
```

##### Template list

Template list command lists all templates.

**Fragment rendering command**

```shell
bin/console app:template:list
```

##### Template show

Template show command displays information about the template, like metadata.

**Fragment rendering command**

```shell
bin/console app:template:show <name>
```

## Fragment's template

Templates of fragments must be declared in folder `templates/fragment`.  
The templates must have a metadata header with their signature.  
Template purpose is provided to fragment for its own purpose, so the template and fragment must share the same purpose at all time.  
Here are all the signature root properties :

- kind: string, always `fragment`.
- label: string, the label/title of the template.
- description: string, the long description of the template.
- purpose: optional string restricted by TemplatePurpose enum, the purpose of the template. E.g. `page`, `article`...
  The use of templates could be restricted by this purpose, so if you expect a page, you can only render a page.
- version: int, the version of the template, increase with breaking changes.
- properties : See [Template Properties](#template-properties)
- children : See [Child Fragment](#child-fragment)

You can find many example but the basic is the header :

```
{#---
kind: fragment
label: "Test template"
description: "Test template for rich content"
version: 1
properties:
    content: rich_text
    items:
      type: list
      items:
          type: object
          properties:
              name: string
              label: string
              target: string
children:
    topmenu: menu-top
---#}
```

## Template Properties

The properties are declared in the template headers; they can be optional or required, but this declaration enforces a strict format, that we call signature.  
They are stored in a json property of the fragment.

Properties can be of different types, including complex types.
By default, properties are required, but you could prefix it with `?` to get it optional.

#### The long signature

Each property type is commonly declared using the short signature, a single string, but you could also use the long one.  
Using the long signature means you can declare the entire configuration explicitly in an object; it is clearly more verbose but also clearer.

**Property configuration reference**

*type* : The property type, as string  
*required* : The property is required, as boolean

#### Types of properties

##### String

The type `string` is a common string.  
This is stored as a string.

##### Rich Text

The type `rich_text` is a more complex piece of HTML, allowing several formats.

You can find all rich text formats in a section below.

##### Object

The type `object` is a dictionary of sub properties, allowing all types of properties.  
It requires the presence of `properties` in signature.

##### List

The type `list` is a list of items, allowing all types of properties. So you can have a list of string, or a list of object.  
It requires the presence of `items` in signature, to declare the signature of items.

#### Rich text formats

Rich text formats are declared in constants `ContentFormatter::FORMAT_*`.

##### HTML

The `html` format allows raw HTML to be rendered in page.

##### Text

The `text` format to render secured text in page, any HTML will be displayed as text.

##### Markdown

The `markdown` format renders Markdown as HTML.

#### Special properties

##### Related entities

There is a dynamic property called `_related` that is referencing related entities. in case of fragment of page, page is holding the relation but when rendering, fragment is requiring the page,
so this page is automatically declared in related properties of the fragment.

## Child Fragment

Fragment children are declared as relation using FragmentLink, the name of relation is important here.

A child may be or may be not a snippet, it means the template declare if it could reuse an existing child fragment as its own child.  
You could need to reuse fragment in multiple part of you website, but sometimes you want it unique.  
By default, children are unique, but you could prefix it with `*` to get it as list, it allows multiple ordered children with this name.

#### The long signature

Each child is commonly declared using the short signature, a single string, but you could also use the long one.  
Using the long signature means you can declare the entire configuration explicitly in an object; it is clearly more verbose but also clearer.

**Child configuration reference**

*template* : The template of the child, as string  
*multiple* : Expecting multiple children, as boolean. The name of this relation is associated with several child fragments, so the template expects a list  
*required* : The child is required, as boolean. _This value is hard-coded, you can not change it for now_

## Rendering fragment

TODO

