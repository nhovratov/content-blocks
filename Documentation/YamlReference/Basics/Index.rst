.. include:: /Includes.rst.txt
.. _basics:

===============
Basics (Mixins)
===============

Basics are a concept like partials or mixins. They are used to have a
pre-defined set of fields that can be reused and have to be defined only once.

There are **two** different ways of using it.

The first way is to use Basics like layouts for your UI. The Basics are added
**after** the fields of your Content Block. This is useful if you want to have
a set of fields that are always available for a Content Block.

List of the standard Basics shipped with Content Blocks:

*  :yaml:`TYPO3/Appearance`
*  :yaml:`TYPO3/Links`
*  :yaml:`TYPO3/Categories`

This is an example on how to add the classic Fluid Styled Content "Appearance"
Tab and the additional "Links" palette.

.. code-block:: yaml
   :caption: EXT:your_extension/ContentBlocks/ContentElements/basics/EditorInterface.yaml

    name: example/basics
    basics:
        - TYPO3/Appearance
        - TYPO3/Links
    fields:
        # - ...

You can add as many Basics as you need. Note, that all Basics are simply
concatenated onto each other. Be careful, not to create an invalid state by
gluing incompatible Basics together.

The second way is to use Basics directly between your custom fields. This can
be done by using the identifier and the type :yaml:`Basic`.

.. code-block:: yaml
   :caption: EXT:your_extension/ContentBlocks/ContentElements/basics/EditorInterface.yaml

    name: example/basics
    basics:
        - TYPO3/Appearance
    fields:
        - identifier: header
          useExistingField: true
        - identifier: TYPO3/Links
          type: Basic

Define own Basics
=================

You can define your own Basics by placing one or more YAML files into
`ContentBlocks/Basics`. The name of the YAML file can be chosen freely.

Example on how to create a single Basic:

.. code-block:: yaml
   :caption: EXT:your_extension/ContentBlocks/Basics/YourBasic.yaml

    identifier: Vendor/YourBasic
    fields:
      - identifier: a_basic_field
        type: Text

The :yaml:`fields` part is exactly the same as in Content Blocks. Here
you can define a Tab, a Palette or simply a set of fields.

The most practical way to use Basics is to use pre-defined tabs as the global
:yaml:`basics` option, so they are always added at the end. The field type
:yaml:`Basic` is used best as a palette. There you can define a set of fields,
which you always need e.g. various header fields.

.. tip::

    Unlike Content Block names, it is not mandatory to provide a vendor name for
    your Basic identifier. However, it is recommended to avoid using too generic
    names to avoid conflicts.
