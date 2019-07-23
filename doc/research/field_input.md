# Query field input
While using that field for reading content is obvious, it may be usable for input as well.

For a simple case such as "children of type X", new items of the given type could
be created as sub-items of a newly added item. Example: create a gallery and all of
its images.

It would imply that a query can be transformed into a ContentCreateStruct. For the
gallery example aboven 

## GraphQL example
Given a `place` content type with a `points_of_interest` query field that returns
the children of the place that are of type `point_of_interest`:

```graphql
mutation CreatePlaceWithPOI {
  createPlace(input: {
    parentLocationId: 2,
    name: "La Super Halle",
    location: {
      address: "Boulevard de l'Europe, Pierre-Bénite"
    },
    pointsOfInterest: [
      { name: "Rayon vrac" }
      { name: "Rayon fruits & légumes" }
      { name: "Rayon bière" }
    ],
  })
}
```

Note that the place should be created first, followed by the points of interest.