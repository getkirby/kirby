<?php

namespace Kirby\Form;

class StructureField extends Field
{
    use Mixins\Counter;
    use Mixins\Fields;
    use Mixins\Help;
    use Mixins\Label;
    use Mixins\Layout;
    use Mixins\MinMax;
    use Mixins\Required;
    use Mixins\Value;

    protected function defaultLayout(): string
    {
        return 'list';
    }

    protected function layouts(): array
    {
        return ['table', 'list', 'cards'];
    }

    protected function form(array $values = [])
    {
        return new Form([
            'fields' => $this->fields(),
            'model'  => $this->model(),
            'values' => $values
        ]);
    }

    protected function validate($value)
    {
        foreach ($value as $index => $row) {
            if (is_array($row) === false) {
                continue;
            }

            $this->form($row)->isValid();
        }

        $this->validateMinMax($value, [
            'max' => 'The structure contains too many items',
            'min' => 'The structure contains not enough items'
        ]);

        return true;
    }

    protected function valueFromInput($input)
    {
        $rows  = $this->valueFromYaml($input);
        $value = [];

        foreach ($rows as $index => $row) {
            if (is_array($row) === false) {
                continue;
            }

            $value[] = $this->form($row)->values();
        }

        return $value;
    }

    protected function valueToString($value): string
    {
        return $this->valueToYaml($value);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['fields'] = $this->form([])->fields()->toOptions();
        return $array;
    }
}
