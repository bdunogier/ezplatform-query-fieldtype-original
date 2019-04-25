# Dynamic relations field: data providers

In v2.0 of the query fieldtype, the usage of a QueryType is hardcoded, and there is no alternative. Future versions should use data providers, where Content Queries are only one data provider type. Another could be recommendation. Any data source that indexes content items can be a data provider.

### Examples

#### Nearby places

Lists places that are within a given distance of a place.

- Source: content query
- Parameters:
  - `selfContentId`: ID of the reference place, will be excluded from results.
  - `latitude`, `longitude`: reference point
  - `distance`: distance from the reference point

#### Gallery images

Lists the images of a gallery.

- Source: content query
- Parameters:
  - `contentId`: ID of the gallery content

### Data providers

When adding a field of that type to a content type, the available data providers are offered in a list. 
Once one is picked, its specific UI, allowing to set the provider's options. More UI items may be added

A data provider will:

- Populate the field definition edition form with its specific requirements
  Example for content query: query types dropdown.
- List the parameters that must be mapped
  Example for content query, once the nearby place query type has been selected: `selfLocationId`, `distance`, `latitude`, `longitude`

Mapping parameters is independent from the data provider. It gives the names and types of parameters, 
and mapping is done in the by generically.

#### More interfaces ?
Should these be different interfaces, so that we can avoid a "data provider" ? It doesn't feel too good.

##### Parameters

###### List parameters
To what purpose ? build the field definition form ? No, there's an API for that.
In any case, it is convenient to be able to introspect a parameter's expectations.

###### Form configuration
Add widgets for mappable parameters to a given field definition edit form.

###### Render parameters
Based on expressions / property accessors, given a location/content (?), return a hash with the parameters values.

##### Retrieve data
When data is requested from the field, the provider is used to retrieve data based on a set of parameters values. They are obtained from the Field Definition config.

```php
$contentIterable = $contentFinder->findContent($fieldDefinition, 0, 10);
```

The `ContentFinder` is unique for the package. Given a `FieldDefinition` with proper configuration,
it is able to use the proper loader to find content items and return them. 



## Requirements

### One fieldtype per provider
Change the fieldtype to register a distinct fieldtype for each registered provider.

Items:
- fieldtype service (tag with alias)
- converter (identical for all)
- search field (identical for all)
- translations ?
- form mappers (tag with alias)
- anything else ?

#### What about templates ? 
Provider should come with its own field definition edit & view templates.
They also inherit from the main one.
They can't be identical.
Settings need to be defined for them.

### Query types introspection

To provide a rich UI to map a QueryType's parameters, Field Types must be introspectable, with parameters name and maybe type. The interface does have a `getParameters()` method, but it only returns the parameters names, no types, description, validation...

In the meantime, add something to the package itself to add introspection to Query Types.

### Other providers ?
Providers return a list of content / location id given a set of parameter / value pairs.

Options:
- Recommendation engine
- Personnalized block ?