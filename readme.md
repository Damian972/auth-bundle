## Description

Simple bundle that helps you to implement some steps of a very basic auth system (_**[Symfony Guard Authenticator](https://symfony.com/doc/current/security/form_login_setup.html)**_) like:

-   Register
-   Confirm Account
-   Reset Password

Look at **`demo`** folder for examples.

## Installation

**1** Add the bundle to your vendor

```shell
...
```

**Note**: still in beta so [load from the repository](https://getcomposer.org/doc/05-repositories.md#loading-a-package-from-a-vcs-repository)

**2** Register the bundle in `config/bundles.php`

```php
$bundles = [
    ...
    Damian972\AuthBundle\AuthBundle::class => ['all' => true],
];
```

**3** Set doctrine config

```yaml
doctrine:
    orm:
        resolve_target_entities:
            Damian972\AuthBundle\Contracts\UserInterface: <your_user_entity>
```

**4** Implement `UserInterface` to your user entity

```php
...
use Damian972\AuthBundle\Contracts\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    ...
}
```

**5** Set the bundle config (**config/packages/auth.yaml**)

```yaml
auth:
    max_login_attempts: 3
    token_expire_after: 30 #in minutes
```

## Events

Events to dispatch manually (see _**demo**_ folder):

-   `Damian972\AuthBundle\Event\AccountConfirmedEvent`
-   `Damian972\AuthBundle\Event\BadCredentialsEvent`
-   `Damian972\AuthBundle\Event\RegisteredUserEvent`

Automatically dispatched:

-   `Damian972\AuthBundle\Event\PasswordRecoveredEvent`
-   `Damian972\AuthBundle\Event\PasswordResetRequestEvent`
