<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Query\Builder;

/**
 * Combined Eloquent and Query Builder methods hint
 *
 * @package `Illuminate\Database\Eloquent\Model` Eloquent Model
 * @package `Illuminate\Database\Query\Builder` Query Builder
 *
 * @requires Laravel 11.1
 *
 * @method static void __construct(array $attributes = []) Create a new Eloquent model instance.
 * @method static $this fill(array $attributes) Fill the model with an array of attributes.
 * @method static $this forceFill(array $attributes) Fill the model with an array of attributes. Force mass assignment.
 * @method static string qualifyColumn($column) Qualify the given column name by the model's table.
 * @method static array qualifyColumns($columns) Qualify the given columns with the model's table.
 * @method static static newInstance($attributes = [], $exists = true) Create a new instance of the given model.
 * @method static static newFromBuilder($attributes = [], $connection = null) Create a new model instance that is existing.
 * @method static $this load($relations) Eager load relations on the model.
 * @method static $this loadMorph($relation, $relations) Eager load relationships on the polymorphic relation of a model.
 * @method static $this loadMissing($relations) Eager load relations on the model if they are not already eager loaded.
 * @method static $this loadAggregate($relations, $column, $function = null) Eager load relation's column aggregations on the model.
 * @method static $this loadCount($relations) Eager load relation counts on the model.
 * @method static $this loadMax($relations, $column) Eager load relation max column values on the model.
 * @method static $this loadMin($relations, $column) Eager load relation min column values on the model.
 * @method static $this loadSum($relations, $column) Eager load relation's column summations on the model.
 * @method static $this loadAvg($relations, $column) Eager load relation average column values on the model.
 * @method static $this loadExists($relations) Eager load related model existence values on the model.
 * @method static $this loadMorphAggregate($relation, $relations, $column, $function = null) Eager load relationship column aggregation on the polymorphic relation of a model.
 * @method static $this loadMorphCount($relation, $relations) Eager load relationship counts on the polymorphic relation of a model.
 * @method static $this loadMorphMax($relation, $relations, $column) Eager load relationship max column values on the polymorphic relation of a model.
 * @method static $this loadMorphMin($relation, $relations, $column) Eager load relationship min column values on the polymorphic relation of a model.
 * @method static $this loadMorphSum($relation, $relations, $column) Eager load relationship column summations on the polymorphic relation of a model.
 * @method static $this loadMorphAvg($relation, $relations, $column) Eager load relationship average column values on the polymorphic relation of a model.
 * @method static bool update(array $attributes = [], array $options = []) Update the model in the database.
 * @method static bool updateOrFail(array $attributes = [], array $options = []) Update the model in the database within a transaction.
 * @method static bool updateQuietly(array $attributes = [], array $options = []) Update the model in the database without raising any events.
 * @method static bool push() Save the model and all of its relationships.
 * @method static bool pushQuietly() Save the model and all of its relationships without raising any events to the parent model.
 * @method static bool saveQuietly(array $options = []) Save the model to the database without raising any events.
 * @method static bool save(array $options = []) Save the model to the database.
 * @method static bool saveOrFail(array $options = []) Save the model to the database within a transaction.
 * @method static bool|null delete() Delete the model from the database.
 * @method static bool deleteQuietly() Delete the model from the database without raising any events.
 * @method static bool|null deleteOrFail() Delete the model from the database within a transaction.
 * @method static bool|null forceDelete() Force a hard delete on a soft deleted model.
 * @method static \Illuminate\Database\Eloquent\Builder newQuery() Get a new query builder for the model's table.
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery() Get a new query builder that doesn't have any global scopes or eager loading.
 * @method static \Illuminate\Database\Eloquent\Builder newQueryWithoutRelationships() Get a new query builder with no relationships loaded.
 * @method static \Illuminate\Database\Eloquent\Builder registerGlobalScopes($builder) Register the global scopes for this builder instance.
 * @method static \Illuminate\Database\Eloquent\Builder|static newQueryWithoutScopes() Get a new query builder that doesn't have any global scopes.
 * @method static \Illuminate\Database\Eloquent\Builder newQueryWithoutScope($scope) Get a new query instance without a given scope.
 * @method static \Illuminate\Database\Eloquent\Builder newQueryForRestoration($ids) Get a new query to restore one or more models by their queueable IDs.
 * @method static \Illuminate\Database\Eloquent\Builder|static newEloquentBuilder($query) Create a new Eloquent query builder for the model.
 * @method static \Illuminate\Database\Eloquent\Collection newCollection(array $models = []) Create a new Eloquent Collection instance.
 * @method static \Illuminate\Database\Eloquent\Relations\Pivot newPivot(self $parent, array $attributes, $table, $exists, $using = null) Create a new pivot model instance.
 * @method static bool hasNamedScope($scope) Determine if the model has a given scope.
 * @method static mixed callNamedScope($scope, array $parameters = []) Apply the given named scope if possible.
 * @method static array toArray() Convert the model instance to an array.
 * @method static string toJson($options = 0) Convert the model instance to JSON.
 * @method static mixed jsonSerialize() Convert the object into something JSON serializable.
 * @method static static|null fresh($with = []) Reload a fresh model instance from the database.
 * @method static $this refresh() Reload the current model instance with fresh attributes from the database.
 * @method static static replicate(array $except = null) Clone the model into a new, non-existing instance.
 * @method static static replicateQuietly(array $except = null) Clone the model into a new, non-existing instance without raising any events.
 * @method static bool is($model) Determine if two models have the same ID and belong to the same table.
 * @method static bool isNot($model) Determine if two models are not the same.
 * @method static \Illuminate\Database\Connection getConnection() Get the database connection for the model.
 * @method static string|null getConnectionName() Get the current connection name for the model.
 * @method static $this setConnection($name) Set the connection associated with the model.
 * @method static string getTable() Get the table associated with the model.
 * @method static $this setTable($table) Set the table associated with the model.
 * @method static string getKeyName() Get the primary key for the model.
 * @method static $this setKeyName($key) Set the primary key for the model.
 * @method static string getQualifiedKeyName() Get the table qualified key name.
 * @method static string getKeyType() Get the auto-incrementing key type.
 * @method static $this setKeyType($type) Set the data type for the primary key.
 * @method static bool getIncrementing() Get the value indicating whether the IDs are incrementing.
 * @method static $this setIncrementing($value) Set whether IDs are incrementing.
 * @method static mixed getKey() Get the value of the model's primary key.
 * @method static mixed getQueueableId() Get the queueable identity for the entity.
 * @method static array getQueueableRelations() Get the queueable relationships for the entity.
 * @method static string|null getQueueableConnection() Get the queueable connection for the entity.
 * @method static mixed getRouteKey() Get the value of the model's route key.
 * @method static string getRouteKeyName() Get the route key for the model.
 * @method static \Illuminate\Database\Eloquent\Model|null resolveRouteBinding($value, $field = null) Retrieve the model for a bound value.
 * @method static \Illuminate\Database\Eloquent\Model|null resolveSoftDeletableRouteBinding($value, $field = null) Retrieve the model for a bound value.
 * @method static \Illuminate\Database\Eloquent\Model|null resolveChildRouteBinding($childType, $value, $field) Retrieve the child model for a bound value.
 * @method static \Illuminate\Database\Eloquent\Model|null resolveSoftDeletableChildRouteBinding($childType, $value, $field) Retrieve the child model for a bound value.
 * @method static \Illuminate\Database\Eloquent\Relations\Relation resolveRouteBindingQuery($query, $value, $field = null) Retrieve the model for a bound value.
 * @method static string getForeignKey() Get the default foreign key name for the model.
 * @method static int getPerPage() Get the number of models to return per page.
 * @method static $this setPerPage($perPage) Set the number of models to return per page.
 * @method static string broadcastChannelRoute() Get the broadcast channel route definition that is associated with the given entity.
 * @method static string broadcastChannel() Get the broadcast channel name that is associated with the given entity.
 * @method static mixed __get($key) Dynamically retrieve attributes on the model.
 * @method static void __set($key, $value) Dynamically set attributes on the model.
 * @method static bool offsetExists($offset) Determine if the given attribute exists.
 * @method static mixed offsetGet($offset) Get the value for a given offset.
 * @method static void offsetSet($offset, $value) Set the value for a given offset.
 * @method static void offsetUnset($offset) Unset the value for a given offset.
 * @method static bool __isset($key) Determine if an attribute or relation exists on the model.
 * @method static void __unset($key) Unset an attribute on the model.
 * @method static mixed __call($method, $parameters) Handle dynamic method calls into the model.
 * @method static string __toString() Convert the model to its string representation.
 * @method static $this escapeWhenCastingToString($escape = true) Indicate that the object's string representation should be escaped when __toString is invoked.
 * @method static array __sleep() Prepare the object for serialization.
 * @method static void __wakeup() When a model is being unserialized, check if it needs to be booted.
 * @method static array attributesToArray() Convert the model's attributes to an array.
 * @method static array relationsToArray() Get the model's relationships in array form.
 * @method static mixed getAttribute($key) Get an attribute from the model.
 * @method static mixed getAttributeValue($key) Get a plain attribute (not a relationship).
 * @method static mixed getRelationValue($key) Get a relationship.
 * @method static bool isRelation($key) Determine if the given key is a relationship method on the model.
 * @method static bool hasGetMutator($key) Determine if a get mutator exists for an attribute.
 * @method static bool hasAttributeMutator($key) Determine if a "Attribute" return type marked mutator exists for an attribute.
 * @method static bool hasAttributeGetMutator($key) Determine if a "Attribute" return type marked get mutator exists for an attribute.
 * @method static $this mergeCasts($casts) Merge new casts with existing casts on the model.
 * @method static mixed setAttribute($key, $value) Set a given attribute on the model.
 * @method static bool hasSetMutator($key) Determine if a set mutator exists for an attribute.
 * @method static bool hasAttributeSetMutator($key) Determine if an "Attribute" return type marked set mutator exists for an attribute.
 * @method static $this fillJsonAttribute($key, $value) Set a given JSON attribute on the model.
 * @method static mixed fromJson($value, $asObject = true) Decode the given JSON back into an array or object.
 * @method static mixed fromEncryptedString($value) Decrypt the given encrypted string.
 * @method static mixed fromFloat($value) Decode the given float.
 * @method static string|null fromDateTime($value) Convert a DateTime to a storable string.
 * @method static array getDates() Get the attributes that should be converted to dates.
 * @method static string getDateFormat() Get the format for database stored dates.
 * @method static $this setDateFormat($format) Set the date format used by the model.
 * @method static bool hasCast($key, $types = null) Determine whether an attribute should be cast to a native type.
 * @method static array getCasts() Get the attributes that should be cast.
 * @method static array getAttributes() Get all of the current attributes on the model.
 * @method static $this setRawAttributes(array $attributes, $sync = true) Set the array of model attributes. No checking is done.
 * @method static mixed|array getOriginal($key = null, $default = null) Get the model's original attribute values.
 * @method static mixed|array getRawOriginal($key = null, $default = null) Get the model's raw original attribute values.
 * @method static array only($attributes) Get a subset of the model's attributes.
 * @method static $this syncOriginal() Sync the original attributes with the current.
 * @method static $this syncOriginalAttribute($attribute) Sync a single original attribute with its current value.
 * @method static $this syncOriginalAttributes($attributes) Sync multiple original attribute with their current values.
 * @method static $this syncChanges() Sync the changed attributes.
 * @method static bool isDirty($attributes = null) Determine if the model or any of the given attribute(s) have been modified.
 * @method static bool isClean($attributes = null) Determine if the model or all the given attribute(s) have remained the same.
 * @method static $this discardChanges() Discard attribute changes and reset the attributes to their original state.
 * @method static bool wasChanged($attributes = null) Determine if the model or any of the given attribute(s) were changed when the model was last saved.
 * @method static array getDirty() Get the attributes that have been changed since the last sync.
 * @method static array getChanges() Get the attributes that were changed when the model was last saved.
 * @method static bool originalIsEquivalent($key) Determine if the new and old values for a given key are equivalent.
 * @method static $this append($attributes) Append attributes to query when building a query.
 * @method static array getAppends() Get the accessors that are being appended to model arrays.
 * @method static $this setAppends(array $appends) Set the accessors to append to model arrays.
 * @method static bool hasAppended($attribute) Return whether the accessor attribute has been appended.
 * @method static array getMutatedAttributes() Get the mutated attributes for a given instance.
 * @method static array getObservableEvents() Get the observable event names.
 * @method static $this setObservableEvents(array $observables) Set the observable event names.
 * @method static void addObservableEvents($observables) Add an observable event name.
 * @method static void removeObservableEvents($observables) Remove an observable event name.
 * @method static array getGlobalScopes() Get the global scopes for this class instance.
 * @method static mixed relationResolver($class, $key) Get the dynamic relation resolver if defined or inherited, or return null.
 * @method static \Illuminate\Database\Eloquent\Relations\HasOne hasOne($related, $foreignKey = null, $localKey = null) Define a one-to-one relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\HasOneThrough hasOneThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null) Define a has-one-through relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\MorphOne morphOne($related, $name, $type = null, $id = null, $localKey = null) Define a polymorphic one-to-one relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\BelongsTo belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null) Define an inverse one-to-one or many relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\MorphTo morphTo($name = null, $type = null, $id = null, $ownerKey = null) Define a polymorphic, inverse one-to-one or many relationship.
 * @method static \Illuminate\Database\Eloquent\PendingHasThroughRelationship through($relationship) Create a pending has-many-through or has-one-through relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\HasMany hasMany($related, $foreignKey = null, $localKey = null) Define a one-to-many relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\HasManyThrough hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null) Define a has-many-through relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\MorphMany morphMany($related, $name, $type = null, $id = null, $localKey = null) Define a polymorphic one-to-many relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\BelongsToMany belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) Define a many-to-many relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\MorphToMany morphToMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null, $inverse = true) Define a polymorphic many-to-many relationship.
 * @method static \Illuminate\Database\Eloquent\Relations\MorphToMany morphedByMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) Define a polymorphic, inverse many-to-many relationship.
 * @method static string joiningTable($related, $instance = null) Get the joining table name for a many-to-many relation.
 * @method static string joiningTableSegment() Get this model's half of the intermediate table name for belongsToMany relationships.
 * @method static bool touches($relation) Determine if the model touches a given relation.
 * @method static void touchOwners() Touch the owning relations of the model.
 * @method static string getMorphClass() Get the class name for polymorphic relations.
 * @method static array getRelations() Get all the loaded relations for the instance.
 * @method static mixed getRelation($relation) Get a specified relationship.
 * @method static bool relationLoaded($key) Determine if the given relation is loaded.
 * @method static $this setRelation($relation, $value) Set the given relationship on the model.
 * @method static $this unsetRelation($relation) Unset a loaded relationship.
 * @method static $this setRelations(array $relations) Set the entire relations array on the model.
 * @method static $this withoutRelations() Duplicate the instance and unset all the loaded relations.
 * @method static $this unsetRelations() Unset all the loaded relations for the instance.
 * @method static array getTouchedRelations() Get the relationships that are touched on save.
 * @method static $this setTouchedRelations(array $touches) Set the relationships that are touched on save.
 * @method static bool touch($attribute = null) Update the model's update timestamp.
 * @method static bool touchQuietly($attribute = null) Update the model's update timestamp without raising any events.
 * @method static $this updateTimestamps() Update the creation and update timestamps.
 * @method static $this setCreatedAt($value) Set the value of the "created at" attribute.
 * @method static $this setUpdatedAt($value) Set the value of the "updated at" attribute.
 * @method static \Illuminate\Support\Carbon freshTimestamp() Get a fresh timestamp for the model.
 * @method static string freshTimestampString() Get a fresh timestamp for the model.
 * @method static bool usesTimestamps() Determine if the model uses timestamps.
 * @method static string|null getCreatedAtColumn() Get the name of the "created at" column.
 * @method static string|null getUpdatedAtColumn() Get the name of the "updated at" column.
 * @method static string|null getQualifiedCreatedAtColumn() Get the fully qualified "created at" column.
 * @method static string|null getQualifiedUpdatedAtColumn() Get the fully qualified "updated at" column.
 * @method static bool usesUniqueIds() Determine if the model uses unique ids.
 * @method static void setUniqueIds() Generate unique keys for the model.
 * @method static string newUniqueId() Generate a new key for the model.
 * @method static array uniqueIds() Get the columns that should receive a unique identifier.
 * @method static array getHidden() Get the hidden attributes for the model.
 * @method static $this setHidden(array $hidden) Set the hidden attributes for the model.
 * @method static array getVisible() Get the visible attributes for the model.
 * @method static $this setVisible(array $visible) Set the visible attributes for the model.
 * @method static $this makeVisible($attributes) Make the given, typically hidden, attributes visible.
 * @method static $this makeVisibleIf($condition, $attributes) Make the given, typically hidden, attributes visible if the given truth test passes.
 * @method static $this makeHidden($attributes) Make the given, typically visible, attributes hidden.
 * @method static $this makeHiddenIf($condition, $attributes) Make the given, typically visible, attributes hidden if the given truth test passes.
 * @method static array getFillable() Get the fillable attributes for the model.
 * @method static $this fillable(array $fillable) Set the fillable attributes for the model.
 * @method static $this mergeFillable(array $fillable) Merge new fillable attributes with existing fillable attributes on the model.
 * @method static array getGuarded() Get the guarded attributes for the model.
 * @method static $this guard(array $guarded) Set the guarded attributes for the model.
 * @method static $this mergeGuarded(array $guarded) Merge new guarded attributes with existing guarded attributes on the model.
 * @method static bool isFillable($key) Determine if the given attribute may be mass assigned.
 * @method static bool isGuarded($key) Determine if the given key is guarded.
 * @method static bool totallyGuarded() Determine if the model is totally guarded.
 * @method static $this select($columns = ["*"]) Set the columns to be selected.
 * @method static $this selectSub($query, $as) Add a subselect expression to the query.
 * @method static $this selectRaw($expression, array $bindings = []) Add a new "raw" select expression to the query.
 * @method static $this fromSub($query, $as) Makes "from" fetch from a subquery.
 * @method static $this fromRaw($expression, $bindings = []) Add a raw from clause to the query.
 * @method static $this addSelect($column) Add a new select column to the query.
 * @method static $this distinct() Force the query to only return distinct results.
 * @method static $this from($table, $as = null) Set the table which the query is targeting.
 * @method static $this useIndex($index) Add an index hint to suggest a query index.
 * @method static $this forceIndex($index) Add an index hint to force a query index.
 * @method static $this ignoreIndex($index) Add an index hint to ignore a query index.
 * @method static $this join($table, $first, $operator = null, $second = null, $type = "inner", $where = true) Add a join clause to the query.
 * @method static $this joinWhere($table, $first, $operator, $second, $type = "inner") Add a "join where" clause to the query.
 * @method static $this joinSub($query, $as, $first, $operator = null, $second = null, $type = "inner", $where = true) Add a subquery join clause to the query.
 * @method static $this joinLateral($query, string $as, string $type = "inner") Add a lateral join clause to the query.
 * @method static $this leftJoinLateral($query, string $as) Add a lateral left join to the query.
 * @method static $this leftJoin($table, $first, $operator = null, $second = null) Add a left join to the query.
 * @method static $this leftJoinWhere($table, $first, $operator, $second) Add a "join where" clause to the query.
 * @method static $this leftJoinSub($query, $as, $first, $operator = null, $second = null) Add a subquery left join to the query.
 * @method static $this rightJoin($table, $first, $operator = null, $second = null) Add a right join to the query.
 * @method static $this rightJoinWhere($table, $first, $operator, $second) Add a "right join where" clause to the query.
 * @method static $this rightJoinSub($query, $as, $first, $operator = null, $second = null) Add a subquery right join to the query.
 * @method static $this crossJoin($table, $first = null, $operator = null, $second = null) Add a "cross join" clause to the query.
 * @method static $this crossJoinSub($query, $as) Add a subquery cross join to the query.
 * @method static $this mergeWheres($wheres, $bindings) Merge an array of where clauses and bindings.
 * @method static $this where($column, $operator = null, $value = null, $boolean = "and") Add a basic where clause to the query.
 * @method static array prepareValueAndOperator($value, $operator, $useDefault = true) Prepare the value and operator for a where clause.
 * @method static $this orWhere($column, $operator = null, $value = null) Add an "or where" clause to the query.
 * @method static $this whereNot($column, $operator = null, $value = null, $boolean = "and") Add a basic "where not" clause to the query.
 * @method static $this orWhereNot($column, $operator = null, $value = null) Add an "or where not" clause to the query.
 * @method static $this whereColumn($first, $operator = null, $second = null, $boolean = "and") Add a "where" clause comparing two columns to the query.
 * @method static $this orWhereColumn($first, $operator = null, $second = null) Add an "or where" clause comparing two columns to the query.
 * @method static $this whereRaw($sql, $bindings = [], $boolean = "and") Add a raw where clause to the query.
 * @method static $this orWhereRaw($sql, $bindings = []) Add a raw or where clause to the query.
 * @method static $this whereIn($column, $values, $boolean = "and", $not = true) Add a "where in" clause to the query.
 * @method static $this orWhereIn($column, $values) Add an "or where in" clause to the query.
 * @method static $this whereNotIn($column, $values, $boolean = "and") Add a "where not in" clause to the query.
 * @method static $this orWhereNotIn($column, $values) Add an "or where not in" clause to the query.
 * @method static $this whereIntegerInRaw($column, $values, $boolean = "and", $not = true) Add a "where in raw" clause for integer values to the query.
 * @method static $this orWhereIntegerInRaw($column, $values) Add an "or where in raw" clause for integer values to the query.
 * @method static $this whereIntegerNotInRaw($column, $values, $boolean = "and") Add a "where not in raw" clause for integer values to the query.
 * @method static $this orWhereIntegerNotInRaw($column, $values) Add an "or where not in raw" clause for integer values to the query.
 * @method static $this whereNull($columns, $boolean = "and", $not = true) Add a "where null" clause to the query.
 * @method static $this orWhereNull($column) Add an "or where null" clause to the query.
 * @method static $this whereNotNull($columns, $boolean = "and") Add a "where not null" clause to the query.
 * @method static $this whereBetween($column, iterable $values, $boolean = "and", $not = true) Add a where between statement to the query.
 * @method static $this whereBetweenColumns($column, array $values, $boolean = "and", $not = true) Add a where between statement using columns to the query.
 * @method static $this orWhereBetween($column, iterable $values) Add an or where between statement to the query.
 * @method static $this orWhereBetweenColumns($column, array $values) Add an or where between statement using columns to the query.
 * @method static $this whereNotBetween($column, iterable $values, $boolean = "and") Add a where not between statement to the query.
 * @method static $this whereNotBetweenColumns($column, array $values, $boolean = "and") Add a where not between statement using columns to the query.
 * @method static $this orWhereNotBetween($column, iterable $values) Add an or where not between statement to the query.
 * @method static $this orWhereNotBetweenColumns($column, array $values) Add an or where not between statement using columns to the query.
 * @method static $this orWhereNotNull($column) Add an "or where not null" clause to the query.
 * @method static $this whereDate($column, $operator, $value = null, $boolean = "and") Add a "where date" statement to the query.
 * @method static $this orWhereDate($column, $operator, $value = null) Add an "or where date" statement to the query.
 * @method static $this whereTime($column, $operator, $value = null, $boolean = "and") Add a "where time" statement to the query.
 * @method static $this orWhereTime($column, $operator, $value = null) Add an "or where time" statement to the query.
 * @method static $this whereDay($column, $operator, $value = null, $boolean = "and") Add a "where day" statement to the query.
 * @method static $this orWhereDay($column, $operator, $value = null) Add an "or where day" statement to the query.
 * @method static $this whereMonth($column, $operator, $value = null, $boolean = "and") Add a "where month" statement to the query.
 * @method static $this orWhereMonth($column, $operator, $value = null) Add an "or where month" statement to the query.
 * @method static $this whereYear($column, $operator, $value = null, $boolean = "and") Add a "where year" statement to the query.
 * @method static $this orWhereYear($column, $operator, $value = null) Add an "or where year" statement to the query.
 * @method static $this whereNested(\Closure $callback, $boolean = "and") Add a nested where statement to the query.
 * @method static \Illuminate\Database\Query\Builder forNestedWhere() Create a new query instance for nested where condition.
 * @method static $this addNestedWhereQuery($query, $boolean = "and") Add another query builder as a nested where to the query builder.
 * @method static $this whereExists($callback, $boolean = "and", $not = true) Add an exists clause to the query.
 * @method static $this orWhereExists($callback, $not = true) Add an or exists clause to the query.
 * @method static $this whereNotExists($callback, $boolean = "and") Add a where not exists clause to the query.
 * @method static $this orWhereNotExists($callback) Add a where not exists clause to the query.
 * @method static $this addWhereExistsQuery(self $query, $boolean = "and", $not = true) Add an exists clause to the query.
 * @method static $this whereRowValues($columns, $operator, $values, $boolean = "and") Adds a where condition using row values.
 * @method static $this orWhereRowValues($columns, $operator, $values) Adds an or where condition using row values.
 * @method static $this whereJsonContains($column, $value, $boolean = "and", $not = true) Add a "where JSON contains" clause to the query.
 * @method static $this orWhereJsonContains($column, $value) Add an "or where JSON contains" clause to the query.
 * @method static $this whereJsonDoesntContain($column, $value, $boolean = "and") Add a "where JSON not contains" clause to the query.
 * @method static $this orWhereJsonDoesntContain($column, $value) Add an "or where JSON not contains" clause to the query.
 * @method static $this whereJsonContainsKey($column, $boolean = "and", $not = true) Add a clause that determines if a JSON path exists to the query.
 * @method static $this orWhereJsonContainsKey($column) Add an "or" clause that determines if a JSON path exists to the query.
 * @method static $this whereJsonDoesntContainKey($column, $boolean = "and") Add a clause that determines if a JSON path does not exist to the query.
 * @method static $this orWhereJsonDoesntContainKey($column) Add an "or" clause that determines if a JSON path does not exist to the query.
 * @method static $this whereJsonLength($column, $operator, $value = null, $boolean = "and") Add a "where JSON length" clause to the query.
 * @method static $this orWhereJsonLength($column, $operator, $value = null) Add an "or where JSON length" clause to the query.
 * @method static $this dynamicWhere($method, $parameters) Handles dynamic "where" clauses to the query.
 * @method static $this whereFullText($columns, $value, array $options = [], $boolean = "and") Add a "where fulltext" clause to the query.
 * @method static $this orWhereFullText($columns, $value, array $options = []) Add a "or where fulltext" clause to the query.
 * @method static $this whereAll($columns, $operator = null, $value = null, $boolean = "and") Add a "where" clause to the query for multiple columns with "and" conditions between them.
 * @method static $this orWhereAll($columns, $operator = null, $value = null) Add an "or where" clause to the query for multiple columns with "and" conditions between them.
 * @method static $this whereAny($columns, $operator = null, $value = null, $boolean = "and") Add an "where" clause to the query for multiple columns with "or" conditions between them.
 * @method static $this orWhereAny($columns, $operator = null, $value = null) Add an "or where" clause to the query for multiple columns with "or" conditions between them.
 * @method static $this groupBy($groups) Add a "group by" clause to the query.
 * @method static $this groupByRaw($sql, array $bindings = []) Add a raw groupBy clause to the query.
 * @method static $this having($column, $operator = null, $value = null, $boolean = "and") Add a "having" clause to the query.
 * @method static $this orHaving($column, $operator = null, $value = null) Add an "or having" clause to the query.
 * @method static $this havingNested(\Closure $callback, $boolean = "and") Add a nested having statement to the query.
 * @method static $this addNestedHavingQuery($query, $boolean = "and") Add another query builder as a nested having to the query builder.
 * @method static $this havingNull($columns, $boolean = "and", $not = true) Add a "having null" clause to the query.
 * @method static $this orHavingNull($column) Add an "or having null" clause to the query.
 * @method static $this havingNotNull($columns, $boolean = "and") Add a "having not null" clause to the query.
 * @method static $this orHavingNotNull($column) Add an "or having not null" clause to the query.
 * @method static $this havingBetween($column, iterable $values, $boolean = "and", $not = true) Add a "having between " clause to the query.
 * @method static $this havingRaw($sql, array $bindings = [], $boolean = "and") Add a raw having clause to the query.
 * @method static $this orHavingRaw($sql, array $bindings = []) Add a raw or having clause to the query.
 * @method static $this orderBy($column, $direction = "asc") Add an "order by" clause to the query.
 * @method static $this orderByDesc($column) Add a descending "order by" clause to the query.
 * @method static $this latest($column = "created_at") Add an "order by" clause for a timestamp to the query.
 * @method static $this oldest($column = "created_at") Add an "order by" clause for a timestamp to the query.
 * @method static $this inRandomOrder($seed = "") Put the query's results in random order.
 * @method static $this orderByRaw($sql, $bindings = []) Add a raw "order by" clause to the query.
 * @method static $this skip($value) Alias to set the "offset" value of the query.
 * @method static $this offset($value) Set the "offset" value of the query.
 * @method static $this take($value) Alias to set the "limit" value of the query.
 * @method static $this limit($value) Set the "limit" value of the query.
 * @method static $this groupLimit($value, $column) Add a "group limit" clause to the query.
 * @method static $this forPage($page, $perPage = 15) Set the limit and offset for a given page.
 * @method static $this forPageBeforeId($perPage = 15, $lastId = 0, $column = "id") Constrain the query to the previous "page" of results before a given ID.
 * @method static $this forPageAfterId($perPage = 15, $lastId = 0, $column = "id") Constrain the query to the next "page" of results after a given ID.
 * @method static $this reorder($column = null, $direction = "asc") Remove all existing orders and optionally add a new order.
 * @method static $this union($query, $all = true) Add a union statement to the query.
 * @method static $this unionAll($query) Add a union all statement to the query.
 * @method static $this lock($value = true) Lock the selected rows in the table.
 * @method static $this lockForUpdate() Lock the selected rows in the table for updating.
 * @method static $this sharedLock() Share lock the selected rows in the table.
 * @method static $this beforeQuery(callable $callback) Register a closure to be invoked before the query is executed.
 * @method static void applyBeforeQueryCallbacks() Invoke the "before query" modification callbacks.
 * @method static string toSql() Get the SQL representation of the query.
 * @method static string toRawSql() Get the raw SQL representation of the query with embedded bindings.
 * @method static mixed|static find($id, $columns = ["*"]) Execute a query for a single record by ID.
 * @method static mixed|static findOr($id, $columns = ["*"], \Closure $callback = null) Execute a query for a single record by ID or call a callback.
 * @method static mixed value($column) Get a single column's value from the first result of a query.
 * @method static mixed rawValue(string $expression, array $bindings = []) Get a single expression value from the first result of a query.
 * @method static mixed soleValue($column) Get a single column's value from the first result of a query if it's the sole matching record.
 * @method static \Illuminate\Support\Collection get($columns = ["*"]) Execute the query as a "select" statement.
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate($perPage = 15, $columns = ["*"], $pageName = "page", $page = null, $total = null) Paginate the given query into a simple paginator.
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate($perPage = 15, $columns = ["*"], $pageName = "page", $page = null) Get a paginator only supporting simple next and previous links.
 * @method static \Illuminate\Contracts\Pagination\CursorPaginator cursorPaginate($perPage = 15, $columns = ["*"], $cursorName = "cursor", $cursor = null) Get a paginator only supporting simple next and previous links.
 * @method static int getCountForPagination($columns = ["*"]) Get the count of the total records for the paginator.
 * @method static \Illuminate\Support\LazyCollection cursor() Get a lazy collection for the given query.
 * @method static \Illuminate\Support\Collection pluck($column, $key = null) Get a collection instance containing the values of a given column.
 * @method static string implode($column, $glue = "") Concatenate values of a given column as a string.
 * @method static bool exists() Determine if any rows exist for the current query.
 * @method static bool doesntExist() Determine if no rows exist for the current query.
 * @method static mixed existsOr(\Closure $callback) Execute the given callback if no rows exist for the current query.
 * @method static mixed doesntExistOr(\Closure $callback) Execute the given callback if rows exist for the current query.
 * @method static int count($columns = "*") Retrieve the "count" result of the query.
 * @method static mixed min($column) Retrieve the minimum value of a given column.
 * @method static mixed max($column) Retrieve the maximum value of a given column.
 * @method static mixed sum($column) Retrieve the sum of the values of a given column.
 * @method static mixed avg($column) Retrieve the average of the values of a given column.
 * @method static mixed average($column) Alias for the "avg" method.
 * @method static mixed aggregate($function, $columns = ["*"]) Execute an aggregate function on the database.
 * @method static float|int numericAggregate($function, $columns = ["*"]) Execute a numeric aggregate function on the database.
 * @method static bool insert(array $values) Insert new records into the database.
 * @method static int insertOrIgnore(array $values) Insert new records into the database while ignoring errors.
 * @method static int insertGetId(array $values, $sequence = null) Insert a new record and get the value of the primary key.
 * @method static int insertUsing(array $columns, $query) Insert new records into the table using a subquery.
 * @method static int insertOrIgnoreUsing(array $columns, $query) Insert new records into the table using a subquery while ignoring errors.
 * @method static int update(array $values) Update records in the database.
 * @method static int updateFrom(array $values) Update records in a PostgreSQL database using the update from syntax.
 * @method static bool updateOrInsert(array $attributes, array $values = []) Insert or update a record matching the attributes, and fill it with values.
 * @method static int upsert(array $values, $uniqueBy, $update = null) Insert new records or update the existing ones.
 * @method static int increment($column, $amount = 1, array $extra = []) Increment a column's value by a given amount.
 * @method static int incrementEach(array $columns, array $extra = []) Increment the given column's values by the given amounts.
 * @method static int decrement($column, $amount = 1, array $extra = []) Decrement a column's value by a given amount.
 * @method static int decrementEach(array $columns, array $extra = []) Decrement the given column's values by the given amounts.
 * @method static int delete($id = null) Delete records from the database.
 * @method static void truncate() Run a truncate statement on the table.
 * @method static \Illuminate\Database\Query\Builder newQuery() Get a new instance of the query builder.
 * @method static array getColumns() Get all of the query builder's columns in a text-only array with all expressions evaluated.
 * @method static \Illuminate\Contracts\Database\Query\Expression raw($value) Create a raw database expression.
 * @method static array getBindings() Get the current query value bindings in a flattened array.
 * @method static array getRawBindings() Get the raw array of bindings.
 * @method static $this setBindings(array $bindings, $type = "where") Set the bindings on the query builder.
 * @method static $this addBinding($value, $type = "where") Add a binding to the query.
 * @method static mixed castBinding($value) Cast the given binding value.
 * @method static $this mergeBindings(self $query) Merge an array of bindings into our bindings.
 * @method static array cleanBindings(array $bindings) Remove all of the expressions from a list of bindings.
 * @method static \Illuminate\Database\ConnectionInterface getConnection() Get the database connection instance.
 * @method static \Illuminate\Database\Query\Processors\Processor getProcessor() Get the database query processor instance.
 * @method static \Illuminate\Database\Query\Grammars\Grammar getGrammar() Get the query grammar instance.
 * @method static $this useWritePdo() Use the "write" PDO connection when executing the query.
 * @method static static clone() Clone the query.
 * @method static static cloneWithout(array $properties) Clone the query without the given properties.
 * @method static static cloneWithoutBindings(array $except) Clone the query without the given bindings.
 * @method static $this dump($args) Dump the current SQL and bindings.
 * @method static $this dumpRawSql() Dump the raw current SQL with embedded bindings.
 * @method static never dd() Die and dump the current SQL and bindings.
 * @method static never ddRawSql() Die and dump the current SQL with embedded bindings.
 * @method static mixed __call($method, $parameters) Handle dynamic method calls into the method.
 * @method static bool chunk($count, callable $callback) Chunk the results of the query.
 * @method static \Illuminate\Support\Collection chunkMap(callable $callback, $count = 1000) Run a map over each item while chunking.
 * @method static bool each(callable $callback, $count = 1000) Execute a callback over each item while chunking.
 * @method static bool chunkById($count, callable $callback, $column = null, $alias = null) Chunk the results of a query by comparing IDs.
 * @method static bool chunkByIdDesc($count, callable $callback, $column = null, $alias = null) Chunk the results of a query by comparing IDs in descending order.
 * @method static bool orderedChunkById($count, callable $callback, $column = null, $alias = null, $descending = true) Chunk the results of a query by comparing IDs in a given order.
 * @method static bool eachById(callable $callback, $count = 1000, $column = null, $alias = null) Execute a callback over each item while chunking by ID.
 * @method static \Illuminate\Support\LazyCollection lazy($chunkSize = 1000) Query lazily, by chunks of the given size.
 * @method static \Illuminate\Support\LazyCollection lazyById($chunkSize = 1000, $column = null, $alias = null) Query lazily, by chunking the results of a query by comparing IDs.
 * @method static \Illuminate\Support\LazyCollection lazyByIdDesc($chunkSize = 1000, $column = null, $alias = null) Query lazily, by chunking the results of a query by comparing IDs in descending order.
 * @method static \Illuminate\Database\Eloquent\Model|object|static|null first($columns = ["*"]) Execute the query and get the first result.
 * @method static \Illuminate\Database\Eloquent\Model|object|static|null sole($columns = ["*"]) Execute the query and get the first result if it's the sole matching record.
 * @method static $this tap($callback) Pass the query to a given callback.
 * @method static $this|TWhenReturnType when($value = null, callable $callback = null, callable $default = null) Apply the callback if the given "value" is (or resolves to) truthy.
 * @method static $this|TUnlessReturnType unless($value = null, callable $callback = null, callable $default = null) Apply the callback if the given "value" is (or resolves to) falsy.
 * @method static \Illuminate\Support\Collection explain() Explains the query.
 * @method static mixed macroCall($method, $parameters) Dynamically handle calls to the class.
 */
class BaseModel extends Model {
	// child models must be prefixed by `BM` - BaseModel
	// universal tables must be prefixed by `unv_`

    use HasFactory;

	//protected $connection = '';
    //protected $table = '';
    protected $primaryKey = 'id';
    protected $keyType = 'integer';
    public $incrementing = true;

    public $timestamps = true;
    protected $dateFormat  = 'Y-m-d H:i:s.u';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [

    ];

    protected $hidden = [

    ];

    protected $attributes = [

    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];


	public function getTable()
	{
		return $this->table;
	}

    public function getConnectionName()
	{
		return $this->connection;
	}

}
