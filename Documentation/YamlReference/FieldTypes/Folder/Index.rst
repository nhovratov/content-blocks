.. include:: /Includes.rst.txt
.. _field_type_folder:

======
Folder
======

:php:`type => 'folder' // TCA`

The :yaml:`Folder` type enables to select one or more folders. Files within
these folders will be resolved automatically and are available in Fluid.

Settings
========

.. confval:: recursive
   :name: folder-recursive

   :Required: false
   :Type: boolean
   :Default: false

   Files in the selected folder will be resolved recursively.

.. confval:: elementBrowserEntryPoints
   :name: folder-elementBrowserEntryPoints

   :Required: false
   :Type: array

   Enables to set an entrypoint, from which to select folders by default.

.. confval:: maxitems
   :name: folder-maxitems

   :Required: false
   :Type: integer

   Maximum number of items. Defaults to a high value. JavaScript record
   validation prevents the record from being saved if the limit is not satisfied.

.. confval:: minitems
   :name: folder-minitems

   :Required: false
   :Type: integer

   Minimum number of items. Defaults to 0. JavaScript record validation
   prevents the record from being saved if the limit is not satisfied.
   The field can be set as required by setting `minitems` to at least 1.

For more advanced configuration refer to the :ref:`TCA documentation <t3tca:columns-folder>`.

Examples
========

Minimal
-------

.. code-block:: yaml

    name: example/folder
    fields:
      - identifier: folder
        type: Folder

Advanced / use case
-------------------

.. code-block:: yaml

    name: example/folder
    fields:
      - identifier: folder
        type: Folder
        recursive: true
        elementBrowserEntryPoints:
          _default: '1:/styleguide/'
        minitems: 1
