# Builder

Builder is utility for creation Repo, Model and Fixture classes.

Use this builder only in development mode!



```
use JasterStary\RModels\Builders\Builder as RBuilder;

```

```

  $builder = new RBuilder('../app/Repos','../app/Models','../app/Fixtures');
  $builder->generate('helpers');
  $builder->generate('quirks');


```

