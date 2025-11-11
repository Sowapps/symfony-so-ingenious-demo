# SoIngenious Design

This documentation is work in progress.  


## Components

Fragment is a block, a section of page, and it could be attached to a static template.  
A fragment could be reusable and used as template of another fragment, so with properties

- Base Template (or free contents)
- N fragments
- Assigned a locale


TemplateFragment is a Fragment using a template with defined contents, multiple properties.  
Example: Testimonial fragment with URL to user picture, a username and the text.  
All properties are identified by a type and a name. But with a fragment, we could also force a template.  
The type list could be useful (list of one type ?), to build a carousel / gallery.  
A free fragment is a TemplateFragment with minimalist template.  
This fragment should only use these types of properties :  

- Single line string (Sub-type email, URL, ... ??)
- Multiline string (Markdown)
- Sub-fragment (ID, or empty if missing)


Page is a registered page with one fragment with a template.  
A page is localized and related to a fragment with same language.  

- Path
- Title
- Fragment (Page template, or one fragment by locale)
- N fragments

Relations between fragments must be symbolized in database, or we could not find back a fragment using another.  

LocalizedUnit is a generic entity that get in relation a localized entity through all languages.  
Example: The same pages in FR and in EN share the same LocalizedUnit  

### Fragment List

In a fragment, you could use a fragment list (e.g. gallery).  
For now, this is not implemented but it could require to add a position field in FragmentChild



### Snippets

A snippet fragment is a reusable fragment with properties, it must be embedded in another fragment.  


## Entities

Fragment :
- properties (json: property, name and type)
- html (rendering cache, longtext ?)
- languageId
- snippet (bool : require properties, it should be embedded in another)
- snippet_fragment_id (id to snippet fragment, template_name should be NULL)
- template_name (name of template, snippet_fragment_id should be NULL)


FragmentChild :
- parentFragmentId
- childFragmentId
- name


Page :
- path
- title
- fragment
- language
- localizedUnit


Language: `\Sowapps\SoCore\Entity\Language`


LocalizedUnit:
- ID (the main purpose is the shared ID)
[- type (enum: page, fragment)] (Page only for now)
