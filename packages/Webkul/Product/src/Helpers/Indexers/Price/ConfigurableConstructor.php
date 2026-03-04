<?php

namespace Webkul\Product\Helpers\Indexers\Price;

class ConfigurableConstructor extends Configurable
{
    // Inherits all price indexing logic from Configurable.
    // Min/max prices are derived from variant prices; constructor ingredient
    // modifiers are applied at cart time, not in the index.
}
