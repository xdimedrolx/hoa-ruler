## Reasons to fork
The Hoa project was archived, and no upgrades or patches are neither provided nor accepted by merge requests.

HOA Packages include some code that is Deprecated for PHP 8.0 and PHP 8.1, but we needed these packages to run on modern PHP versions.

## What's changed in from?
The changes mainly affected the return data type hinting in methods declaration, access to uninitialized properties.

## How to use
We've currently forked the following packages, primarily to make hoa/ruler work with PHP 8.1:

<table>
<tr><th>Original package</th><th>Forked package</th></tr>
<tr><td>hoa/ruler</td><td>hiqdev/hoa-ruler</td></tr>
<tr><td>hoa/compiler</td><td>hiqdev/hoa-compiler</td></tr>
<tr><td>hoa/protocol</td><td>hiqdev/hoa-protocol</td></tr>
<tr><td>hoa/iterator</td><td>hiqdev/hoa-iterator</td></tr>
</table>
You can simply replace requirements in composer.json from hoa packages to the corresponding forked packages: there is no need to change something in the codebase.

If you use someone's package, that requires hoa – simply add forks to your project root composer.json: we have marked forks as a replacement, so composer will install them instead of the original packages.

## Versions
We've forked from the latest hoa package versions and bump own versions starting from 1.0.


## Testing

Before running the test suites, the development dependencies must be installed:

```sh
$ composer install
```

Then, to run all the test suites:

```sh
$ vendor/bin/hoa test:run
```

For more information, please read the [contributor
guide](https://hoa-project.net/Literature/Contributor/Guide.html).

## Quick usage

As a quick overview, we propose to see a very simple example that manipulates a
simple rule with a simple context. After, we will add a new operator in the
rule. And finally, we will see how to save a rule in a database.

### Three steps

So first, we create a context with two variables: `group` and `points`, and we
then assert a rule. A context holds values to concretize a rule. A value can
also be the result of a callable. Thus:

```php
$ruler = new Hoa\Ruler\Ruler();

// 1. Write a rule.
$rule  = 'group in ["customer", "guest"] and points > 30';

// 2. Create a context.
$context           = new Hoa\Ruler\Context();
$context['group']  = 'customer';
$context['points'] = function () {
    return 42;
};

// 3. Assert!
var_dump(
    $ruler->assert($rule, $context)
);

/**
 * Will output:
 *     bool(true)
 */
```

In the next example, we have a `User` object and a context that is populated
dynamically (when the `user` variable is concretized, two new variables, `group`
and `points` are created). Moreover, we will create a new operator/function
called `logged`. There is no difference between an operator and a function
except that an operator has two operands (so arguments).

### Adding operators and functions

For now, we have the following operators/functions by default: `and`, `or`,
`xor`, `not`, `=` (`is` as an alias), `!=`, `>`, `>=`, `<`, `<=`, `in` and
`sum`. We can add our own by different way. The simplest and volatile one is
given in the following example. Thus:

```php
// The User object.
class User
{
    const DISCONNECTED = 0;
    const CONNECTED    = 1;

    public $group      = 'customer';
    public $points     = 42;
    protected $_status = 1;

    public function getStatus()
    {
        return $this->_status;
    }
}

$ruler = new Hoa\Ruler\Ruler();

// New rule.
$rule  = 'logged(user) and group in ["customer", "guest"] and points > 30';

// New context.
$context         = new Hoa\Ruler\Context();
$context['user'] = function () use ($context) {
    $user              = new User();
    $context['group']  = $user->group;
    $context['points'] = $user->points;

    return $user;
};

// We add the logged() operator.
$ruler->getDefaultAsserter()->setOperator('logged', function (User $user) {
    return $user::CONNECTED === $user->getStatus();
});

// Finally, we assert the rule.
var_dump(
    $ruler->assert($rule, $context)
);

/**
 * Will output:
 *     bool(true)
 */
```

Also, if a variable in the context is an array, we can access to its values from
a rule with the same syntax as PHP. For example, if the `a` variable is an
array, we can write `a[0]` to access to the value associated to the `0` key. It
works as an hashmap (PHP array implementation), so we can have strings & co. as
keys. In the same way, if a variable is an object, we can call a method on it.
For example, if the `a` variable is an array where the value associated to the
first key is an object with a `foo` method, we can write: `a[0].foo(b)` where
`b` is another variable in the context. Also, we can access to the public
attributes of an object. Obviously, we can mixe array and object accesses.
Please, take a look at the grammar (`hoa://Library/Ruler/Grammar.pp`) to see all
the possible constructions.

### Saving a rule

Now, we have two options to save the rule, for example, in a database. Either we
save the rule as a string directly, or we will save the serialization of the
rule which will avoid further interpretations. In the next example, we see how
to serialize and unserialize a rule by using the `Hoa\Ruler\Ruler::interpret`
static method:

```php
$database->save(
    serialize(
        Hoa\Ruler\Ruler::interpret(
            'logged(user) and group in ["customer", "guest"] and points > 30'
        )
    )
);
```

And for next executions:

```php
$rule = unserialize($database->read());
var_dump(
    $ruler->assert($rule, $context)
);
```

When a rule is interpreted, its object model is created. We serialize and
unserialize this model. To see the PHP code needed to create such a model, we
can print the model itself (as an example). Thus:

```php
echo Hoa\Ruler\Ruler::interpret(
    'logged(user) and group in ["customer", "guest"] and points > 30'
);

/**
 * Will output:
 *     $model = new \Hoa\Ruler\Model();
 *     $model->expression =
 *         $model->and(
 *             $model->func(
 *                 'logged',
 *                 $model->variable('user')
 *             ),
 *             $model->and(
 *                 $model->in(
 *                     $model->variable('group'),
 *                     [
 *                         'customer',
 *                         'guest'
 *                     ]
 *                 ),
 *                 $model->{'>'}(
 *                     $model->variable('points'),
 *                     30
 *                 )
 *             )
 *         );
 */
```

Have fun!

## Documentation

The
[hack book of `Hoa\Ruler`](https://central.hoa-project.net/Documentation/Library/Ruler) contains
detailed information about how to use this library and how it works.

To generate the documentation locally, execute the following commands:

```sh
$ composer require --dev hoa/devtools
$ vendor/bin/hoa devtools:documentation --open
```

More documentation can be found on the project's website:
[hoa-project.net](https://hoa-project.net/).

## Getting help

There are mainly two ways to get help:

  * On the [`#hoaproject`](https://webchat.freenode.net/?channels=#hoaproject)
    IRC channel,
  * On the forum at [users.hoa-project.net](https://users.hoa-project.net).

## Contribution

Do you want to contribute? Thanks! A detailed [contributor
guide](https://hoa-project.net/Literature/Contributor/Guide.html) explains
everything you need to know.

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](https://hoa-project.net/LICENSE) for details.

## Related projects

The following projects are using this library:

  * [RulerZ](https://github.com/K-Phoen/rulerz), Powerful implementation of the
    Specification pattern in PHP,
  * [ownCloud](https://owncloud.org/), A safe home for all your data,
  * [PhpMetrics](http://www.phpmetrics.org/), Static analysis tool for PHP,
  * [`hiqdev/php-billing`](https://github.com/hiqdev/php-billing), a billing library in PHP 
  * [`atoum/ruler-extension`](https://github.com/atoum/ruler-extension), This
    extension allows to filter test results in [atoum](http://atoum.org/).
