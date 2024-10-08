.. include:: /Includes.rst.txt
.. _cb_definition_source:

=========
templates
=========

The **templates** folder contains private resources. If you are familiar with the
directory structure of extensions, this would be the **Resources/Private**
folder. There is a limited set of directories and files, which you can place
here.

backend-preview.html
====================

The **backend-preview.html** can be added to customize the backend preview for
your editors. By default, TYPO3 comes with a standard preview renderer. However,
it is specialized in rendering the preview of Core Content Elements. This means
only Core fields like :sql:`header`, :sql:`subheader` or :sql:`bodytext` are
considered. Therefore, it is advised to provide an own preview for custom
Content Elements. Previews for **Page Types** are displayed at the top of the
content area and beneath the page title. Previews for **Record Types** can only
be shown as nested child records of Content Elements in the Page Module like so:

.. code-block:: html

    <f:comment>Provide the identifier of the child Collection to render a grid preview</f:comment>
    <f:render partial="PageLayout/Grid" arguments="{data: data, identifier: 'tabs_item'}"/>

.. note::

   In backend context, all hidden relations like Collections or file references
   are displayed by default. Thus, the integrator should style those hidden
   elements accordingly or simply not render them.

   .. code-block:: html

      <!-- Hidden relations like Collections -->
      <f:for each="{data.relations}" as="item">
          <f:if condition="{item.systemProperties.disabled}"><!-- Style or hide --></f:if>
      </f:for>

      <!-- Hidden file references -->
      <f:for each="{data.images}" as="file">
          <f:if condition="{file.properties.hidden}"><!-- Style or hide --></f:if>
      </f:for>

See also:

*  Learn more about :ref:`templating <cb_templating>`.
*  Learn how to include :ref:`shared partials <editor_preview_partials>`

frontend.html
=============

This is the default frontend rendering definition for :ref:`Content Elements <yaml_reference_content_element>`.
You can access your fields by the variable :html:`{data}`.

Learn more about :ref:`templating <cb_templating>`.

partials
========

For larger Content Elements, you can divide your **frontend.html** template into
smaller chunks by creating separate partials here.

Partials are included as you normally would in any Fluid template.

.. note::

   Due to current Fluid restrictions, partials have to start with an uppercase
   letter. This restriction might be lifted in later Fluid versions (v5 or above).


.. code-block:: html

   <f:render partial="Component" arguments="{_all}"/>

See also:

*  Learn how to :ref:`share partials <cb_extension_partials>` between Content Blocks.

layouts
=======

You can also add layouts to your Content Block if needed.

.. code-block:: html

   <f:layout name="MyLayout">
