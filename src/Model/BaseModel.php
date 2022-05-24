<?php

namespace Rguj\Laracore\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static void __construct(\Illuminate\Database\ConnectionInterface $connection, \Illuminate\Database\Query\Grammars\Grammar $grammar = null, \Illuminate\Database\Query\Processors\Processor $processor = null) Create a new query builder instance.
 * @method static \Illuminate\Database\Eloquent\Model|static make(array $attributes = []) Create and return an un-saved model instance.
 * @method static $this withGlobalScope($identifier, $scope) Register a new global scope.
 * @method static $this withoutGlobalScope($scope) Remove a registered global scope.
 * @method static $this withoutGlobalScopes(array $scopes = null) Remove all or passed registered global scopes.
 * @method static array removedScopes() Get an array of global scopes that were removed from the query.
 * @method static $this whereKey($id) Add a where clause on the primary key to the query.
 * @method static $this whereKeyNot($id) Add a where clause on the primary key to the query.
 * @method static $this where($column, $operator = null, $value = null, $boolean = "and") Add a basic where clause to the query.
 * @method static \Illuminate\Database\Eloquent\Model|static|null firstWhere($column, $operator = null, $value = null, $boolean = "and") Add a basic where clause to the query, and return the first result.
 * @method static $this orWhere($column, $operator = null, $value = null) Add an "or where" clause to the query.
 * @method static $this whereNot($column, $operator = null, $value = null, $boolean = "and") Add a basic "where not" clause to the query.
 * @method static $this orWhereNot($column, $operator = null, $value = null) Add an "or where not" clause to the query.
 * @method static $this latest($column = "created_at") Add an "order by" clause for a timestamp to the query.
 * @method static $this oldest($column = "created_at") Add an "order by" clause for a timestamp to the query.
 * @method static \Illuminate\Database\Eloquent\Collection hydrate(array $items) Create a collection of models from plain arrays.
 * @method static \Illuminate\Database\Eloquent\Collection fromQuery($query, $bindings = []) Create a collection of models from a raw query.
 * @method static mixed|static find($id, $columns = ["*"]) Execute a query for a single record by ID.
 * @method static \Illuminate\Database\Eloquent\Collection findMany($ids, $columns = ["*"]) Find multiple models by their primary keys.
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[] findOrFail($id, $columns = ["*"]) Find a model by its primary key or throw an exception.
 * @method static \Illuminate\Database\Eloquent\Model|static findOrNew($id, $columns = ["*"]) Find a model by its primary key or return fresh model instance.
 * @method static mixed|static findOr($id, $columns = ["*"], \Closure $callback = null) Execute a query for a single record by ID or call a callback.
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrNew(array $attributes = [], array $values = []) Get the first record matching the attributes or instantiate it.
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrCreate(array $attributes = [], array $values = []) Get the first record matching the attributes or create it.
 * @method static \Illuminate\Database\Eloquent\Model|static updateOrCreate(array $attributes, array $values = []) Create or update a record matching the attributes, and fill it with values.
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrFail($columns = ["*"]) Execute the query and get the first result or throw an exception.
 * @method static \Illuminate\Database\Eloquent\Model|static|mixed firstOr($columns = ["*"], \Closure $callback = null) Execute the query and get the first result or call a callback.
 * @method static \Illuminate\Database\Eloquent\Model|object|static|null sole($columns = ["*"]) Execute the query and get the first result if it's the sole matching record.
 * @method static mixed value($column) Get a single column's value from the first result of a query.
 * @method static mixed soleValue($column) Get a single column's value from the first result of a query if it's the sole matching record.
 * @method static mixed valueOrFail($column) Get a single column's value from the first result of the query or throw an exception.
 * @method static \Illuminate\Support\Collection get($columns = ["*"]) Execute the query as a "select" statement.
 * @method static \Illuminate\Database\Eloquent\Model[]|static[] getModels($columns = ["*"]) Get the hydrated models without eager loading.
 * @method static array eagerLoadRelations(array $models) Eager load the relationships for the models.
 * @method static \Illuminate\Database\Eloquent\Relations\Relation getRelation($name) Get the relation instance for the given relation name.
 * @method static \Illuminate\Support\LazyCollection cursor() Get a lazy collection for the given query.
 * @method static \Illuminate\Support\Collection pluck($column, $key = null) Get a collection instance containing the values of a given column.
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate($perPage = 15, $columns = ["*"], $pageName = "page", $page = null) Paginate the given query into a simple paginator.
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate($perPage = 15, $columns = ["*"], $pageName = "page", $page = null) Get a paginator only supporting simple next and previous links.
 * @method static \Illuminate\Contracts\Pagination\CursorPaginator cursorPaginate($perPage = 15, $columns = ["*"], $cursorName = "cursor", $cursor = null) Get a paginator only supporting simple next and previous links.
 * @method static \Illuminate\Database\Eloquent\Model|$this create(array $attributes = []) Save a new model and return the instance.
 * @method static \Illuminate\Database\Eloquent\Model|$this forceCreate(array $attributes) Save a new model and return the instance. Allow mass-assignment.
 * @method static int update(array $values) Update records in the database.
 * @method static int upsert(array $values, $uniqueBy, $update = null) Insert new records or update the existing ones.
 * @method static int increment($column, $amount = 1, array $extra = []) Increment a column's value by a given amount.
 * @method static int decrement($column, $amount = 1, array $extra = []) Decrement a column's value by a given amount.
 * @method static int delete($id = null) Delete records from the database.
 * @method static mixed forceDelete() Run the default delete function on the builder.
 * @method static void onDelete(\Closure $callback) Register a replacement for the default delete function.
 * @method static bool hasNamedScope($scope) Determine if the given model has a scope.
 * @method static static|mixed scopes($scopes) Call the given local model scopes.
 * @method static static applyScopes() Apply the scopes to the Eloquent builder instance and return it.
 * @method static $this with($relations, $callback = null) Set the relationships that should be eager loaded.
 * @method static $this without($relations) Prevent the specified relations from being eager loaded.
 * @method static $this withOnly($relations) Set the relationships that should be eager loaded while removing any previously added eager loading specifications.
 * @method static \Illuminate\Database\Eloquent\Model|static newModelInstance($attributes = []) Create a new instance of the model being queried.
 * @method static $this withCasts($casts) Apply query-time casts to the model instance.
 * @method static \Illuminate\Database\Query\Builder getQuery() Get the underlying query builder instance.
 * @method static $this setQuery($query) Set the underlying query builder instance.
 * @method static \Illuminate\Database\Query\Builder toBase() Get a base query builder instance.
 * @method static array getEagerLoads() Get the relationships being eagerly loaded.
 * @method static $this setEagerLoads(array $eagerLoad) Set the relationships being eagerly loaded.
 * @method static $this withoutEagerLoads() Flush the relationships being eagerly loaded.
 * @method static \Illuminate\Database\Eloquent\Model|static getModel() Get the model instance being queried.
 * @method static $this setModel(\Illuminate\Database\Eloquent\Model $model) Set a model instance for the model being queried.
 * @method static string qualifyColumn($column) Qualify the given column name by the model's table.
 * @method static array qualifyColumns($columns) Qualify the given columns with the model's table.
 * @method static \Closure getMacro($name) Get the given macro by name.
 * @method static bool hasMacro($name) Checks if a macro is registered.
 * @method static mixed __get($key) Dynamically access builder proxies.
 * @method static mixed __call($method, $parameters) Handle dynamic method calls into the method.
 * @method static static clone() Clone the query.
 * @method static void __clone() Force a clone of the underlying query builder when cloning.
 * @method static bool chunk($count, callable $callback) Chunk the results of the query.
 * @method static \Illuminate\Support\Collection chunkMap(callable $callback, $count = 1000) Run a map over each item while chunking.
 * @method static bool each(callable $callback, $count = 1000) Execute a callback over each item while chunking.
 * @method static bool chunkById($count, callable $callback, $column = null, $alias = null) Chunk the results of a query by comparing IDs.
 * @method static bool eachById(callable $callback, $count = 1000, $column = null, $alias = null) Execute a callback over each item while chunking by ID.
 * @method static \Illuminate\Support\LazyCollection lazy($chunkSize = 1000) Query lazily, by chunks of the given size.
 * @method static \Illuminate\Support\LazyCollection lazyById($chunkSize = 1000, $column = null, $alias = null) Query lazily, by chunking the results of a query by comparing IDs.
 * @method static \Illuminate\Support\LazyCollection lazyByIdDesc($chunkSize = 1000, $column = null, $alias = null) Query lazily, by chunking the results of a query by comparing IDs in descending order.
 * @method static \Illuminate\Database\Eloquent\Model|object|static|null first($columns = ["*"]) Execute the query and get the first result.
 * @method static \Illuminate\Database\Eloquent\Model|object|static|null baseSole($columns = ["*"]) Execute the query and get the first result if it's the sole matching record.
 * @method static $this tap($callback) Pass the query to a given callback.
 * @method static $this|TWhenReturnType when($value, callable $callback = null, callable $default = null) Apply the callback if the given "value" is (or resolves to) truthy.
 * @method static $this|TUnlessReturnType unless($value, callable $callback = null, callable $default = null) Apply the callback if the given "value" is (or resolves to) falsy.
 * @method static \Illuminate\Database\Eloquent\Builder|static has($relation, $operator = ">=", $count = 1, $boolean = "and", \Closure $callback = null) Add a relationship count / exists condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orHas($relation, $operator = ">=", $count = 1) Add a relationship count / exists condition to the query with an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static doesntHave($relation, $boolean = "and", \Closure $callback = null) Add a relationship count / exists condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orDoesntHave($relation) Add a relationship count / exists condition to the query with an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHas($relation, \Closure $callback = null, $operator = ">=", $count = 1) Add a relationship count / exists condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereHas($relation, \Closure $callback = null, $operator = ">=", $count = 1) Add a relationship count / exists condition to the query with where clauses and an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDoesntHave($relation, \Closure $callback = null) Add a relationship count / exists condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereDoesntHave($relation, \Closure $callback = null) Add a relationship count / exists condition to the query with where clauses and an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static hasMorph($relation, $types, $operator = ">=", $count = 1, $boolean = "and", \Closure $callback = null) Add a polymorphic relationship count / exists condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orHasMorph($relation, $types, $operator = ">=", $count = 1) Add a polymorphic relationship count / exists condition to the query with an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static doesntHaveMorph($relation, $types, $boolean = "and", \Closure $callback = null) Add a polymorphic relationship count / exists condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orDoesntHaveMorph($relation, $types) Add a polymorphic relationship count / exists condition to the query with an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHasMorph($relation, $types, \Closure $callback = null, $operator = ">=", $count = 1) Add a polymorphic relationship count / exists condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereHasMorph($relation, $types, \Closure $callback = null, $operator = ">=", $count = 1) Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDoesntHaveMorph($relation, $types, \Closure $callback = null) Add a polymorphic relationship count / exists condition to the query with where clauses.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereDoesntHaveMorph($relation, $types, \Closure $callback = null) Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
 * @method static \Illuminate\Database\Eloquent\Builder|static whereRelation($relation, $column, $operator = null, $value = null) Add a basic where clause to a relationship query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereRelation($relation, $column, $operator = null, $value = null) Add an "or where" clause to a relationship query.
 * @method static \Illuminate\Database\Eloquent\Builder|static whereMorphRelation($relation, $types, $column, $operator = null, $value = null) Add a polymorphic relationship condition to the query with a where clause.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereMorphRelation($relation, $types, $column, $operator = null, $value = null) Add a polymorphic relationship condition to the query with an "or where" clause.
 * @method static \Illuminate\Database\Eloquent\Builder|static whereMorphedTo($relation, $model, $boolean = "and") Add a morph-to relationship condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNotMorphedTo($relation, $model, $boolean = "and") Add a not morph-to relationship condition to the query.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereMorphedTo($relation, $model) Add a morph-to relationship condition to the query with an "or where" clause.
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereNotMorphedTo($relation, $model) Add a not morph-to relationship condition to the query with an "or where" clause.
 * @method static $this whereBelongsTo($related, $relationshipName = null, $boolean = "and") Add a "belongs to" relationship where clause to the query.
 * @method static $this orWhereBelongsTo($related, $relationshipName = null) Add an "BelongsTo" relationship with an "or where" clause to the query.
 * @method static $this withAggregate($relations, $column, $function = null) Add subselect queries to include an aggregate value for a relationship.
 * @method static $this withCount($relations) Add subselect queries to count the relations.
 * @method static $this withMax($relation, $column) Add subselect queries to include the max of the relation's column.
 * @method static $this withMin($relation, $column) Add subselect queries to include the min of the relation's column.
 * @method static $this withSum($relation, $column) Add subselect queries to include the sum of the relation's column.
 * @method static $this withAvg($relation, $column) Add subselect queries to include the average of the relation's column.
 * @method static $this withExists($relation) Add subselect queries to include the existence of related models.
 * @method static \Illuminate\Database\Eloquent\Builder|static mergeConstraintsFrom(\Illuminate\Database\Eloquent\Builder $from) Merge the where constraints from another query to the current query.
 * @method static $this select($columns = ["*"]) Set the columns to be selected.
 * @method static $this selectSub($query, $as) Add a subselect expression to the query.
 * @method static $this selectRaw($expression, array $bindings = []) Add a new "raw" select expression to the query.
 * @method static $this fromSub($query, $as) Makes "from" fetch from a subquery.
 * @method static $this fromRaw($expression, $bindings = []) Add a raw from clause to the query.
 * @method static $this addSelect($column) Add a new select column to the query.
 * @method static $this distinct() Force the query to only return distinct results.
 * @method static $this from($table, $as = null) Set the table which the query is targeting.
 * @method static $this join($table, $first, $operator = null, $second = null, $type = "inner", $where = true) Add a join clause to the query.
 * @method static $this joinWhere($table, $first, $operator, $second, $type = "inner") Add a "join where" clause to the query.
 * @method static $this joinSub($query, $as, $first, $operator = null, $second = null, $type = "inner", $where = true) Add a subquery join clause to the query.
 * @method static $this leftJoin($table, $first, $operator = null, $second = null) Add a left join to the query.
 * @method static $this leftJoinWhere($table, $first, $operator, $second) Add a "join where" clause to the query.
 * @method static $this leftJoinSub($query, $as, $first, $operator = null, $second = null) Add a subquery left join to the query.
 * @method static $this rightJoin($table, $first, $operator = null, $second = null) Add a right join to the query.
 * @method static $this rightJoinWhere($table, $first, $operator, $second) Add a "right join where" clause to the query.
 * @method static $this rightJoinSub($query, $as, $first, $operator = null, $second = null) Add a subquery right join to the query.
 * @method static $this crossJoin($table, $first = null, $operator = null, $second = null) Add a "cross join" clause to the query.
 * @method static $this crossJoinSub($query, $as) Add a subquery cross join to the query.
 * @method static void mergeWheres($wheres, $bindings) Merge an array of where clauses and bindings.
 * @method static array prepareValueAndOperator($value, $operator, $useDefault = true) Prepare the value and operator for a where clause.
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
 * @method static $this whereExists(\Closure $callback, $boolean = "and", $not = true) Add an exists clause to the query.
 * @method static $this orWhereExists(\Closure $callback, $not = true) Add an or exists clause to the query.
 * @method static $this whereNotExists(\Closure $callback, $boolean = "and") Add a where not exists clause to the query.
 * @method static $this orWhereNotExists(\Closure $callback) Add a where not exists clause to the query.
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
 * @method static $this havingBetween($column, array $values, $boolean = "and", $not = true) Add a "having between " clause to the query.
 * @method static $this havingRaw($sql, array $bindings = [], $boolean = "and") Add a raw having clause to the query.
 * @method static $this orHavingRaw($sql, array $bindings = []) Add a raw or having clause to the query.
 * @method static $this orderBy($column, $direction = "asc") Add an "order by" clause to the query.
 * @method static $this orderByDesc($column) Add a descending "order by" clause to the query.
 * @method static $this inRandomOrder($seed = "") Put the query's results in random order.
 * @method static $this orderByRaw($sql, $bindings = []) Add a raw "order by" clause to the query.
 * @method static $this skip($value) Alias to set the "offset" value of the query.
 * @method static $this offset($value) Set the "offset" value of the query.
 * @method static $this take($value) Alias to set the "limit" value of the query.
 * @method static $this limit($value) Set the "limit" value of the query.
 * @method static $this forPage($page, $perPage = 15) Set the limit and offset for a given page.
 * @method static $this forPageBeforeId($perPage = 15, $lastId = 0, $column = "id") Constrain the query to the previous "page" of results before a given ID.
 * @method static $this forPageAfterId($perPage = 15, $lastId = 0, $column = "id") Constrain the query to the next "page" of results after a given ID.
 * @method static $this reorder($column = null, $direction = "asc") Remove all existing orders and optionally add a new order.
 * @method static $this union($query, $all = true) Add a union statement to the query.
 * @method static $this unionAll($query) Add a union all statement to the query.
 * @method static $this lock($value = true) Lock the selected rows in the table.
 * @method static \Illuminate\Database\Query\Builder lockForUpdate() Lock the selected rows in the table for updating.
 * @method static \Illuminate\Database\Query\Builder sharedLock() Share lock the selected rows in the table.
 * @method static $this beforeQuery(callable $callback) Register a closure to be invoked before the query is executed.
 * @method static void applyBeforeQueryCallbacks() Invoke the "before query" modification callbacks.
 * @method static string toSql() Get the SQL representation of the query.
 * @method static int getCountForPagination($columns = ["*"]) Get the count of the total records for the paginator.
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
 * @method static int updateFrom(array $values) Update records in a PostgreSQL database using the update from syntax.
 * @method static bool updateOrInsert(array $attributes, array $values = []) Insert or update a record matching the attributes, and fill it with values.
 * @method static void truncate() Run a truncate statement on the table.
 * @method static \Illuminate\Database\Query\Builder newQuery() Get a new instance of the query builder.
 * @method static \Illuminate\Database\Query\Expression raw($value) Create a raw database expression.
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
 * @method static static cloneWithout(array $properties) Clone the query without the given properties.
 * @method static static cloneWithoutBindings(array $except) Clone the query without the given bindings.
 * @method static $this dump() Dump the current SQL and bindings.
 * @method static never dd() Die and dump the current SQL and bindings.
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