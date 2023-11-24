.. include:: /Includes.rst.txt
.. _field_type_checkbox:

========
Checkbox
========

:php:`type => 'check' // TCA`
:yaml:`alternativeSql: true`

The :yaml:`Checkbox` type generates one or more checkbox fields.

Settings
========

.. confval:: default

   :Required: false
   :Type: integer (bit value)
   :Default: 0

   The default value corresponds to a bit value. If you only have one checkbox
   having 1 or 0 will work to turn it on or off by default. For more than one
   checkbox you need to calculate the bit representation.

.. confval:: items

   :Required: false
   :Type: array

   Only necessary if more than one checkbox is desired. Contains the checkbox
   elements as separate array items. The `label` can also be defined as a
   LLL-reference.

   Example:

   .. code-block:: yaml

      items:
        - label: 'The first'
        - label: 'The second'
        - label: 'The third'

For advanced configuration refer to the :ref:`TCA documentation <t3tca:columns-check>`.

Examples
========

Minimal
-------

.. code-block:: yaml

    name: example/checkbox
    fields:
      - identifier: checkbox
        type: Checkbox

Advanced / use case
-------------------

Multiple checkboxes:

.. code-block:: yaml

    name: example/checkbox
    fields:
      - identifier: checkbox
        type: Checkbox
        items:
          - label: 'The first'
          - label: 'The second'
          - label: 'The third'
        default: 2
        cols: 3

Toggle checkbox:

.. code-block:: yaml

    name: example/checkbox
    fields:
      - identifier: toggle
        type: Checkbox
        renderType: checkboxToggle
        default: 1