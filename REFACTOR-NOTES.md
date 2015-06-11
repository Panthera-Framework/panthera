- global $panthera       =>  Panthera\pantheraCore::getInstance()
- pantheraClass          =>  Panthera\baseClass
- $a -> b ()             =>  $a->b()

    regex for phpStorm search & replace:
    \$([A-Za-z]+)([ ]?)->([ ]?)
    \\$$1->

    " -> "
    "->"