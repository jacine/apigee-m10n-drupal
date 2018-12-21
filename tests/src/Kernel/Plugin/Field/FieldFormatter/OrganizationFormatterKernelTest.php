<?php

/*
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\Tests\apigee_m10n\Kernel\Plugin\Field\FieldFormatter;

use Drupal\apigee_m10n\Plugin\Field\FieldFormatter\OrganizationFormatter;
use Drupal\apigee_m10n\Plugin\Field\FieldType\OrganizationFieldItem;
use Drupal\Core\Field\FieldItemList;
use Drupal\Tests\apigee_m10n\Kernel\MonetizationKernelTestBase;

/**
 * Test the `apigee_organization` field formatter.
 *
 * @group apigee_m10n
 * @group apigee_m10n_kernel
 */
class OrganizationFormatterKernelTest extends MonetizationKernelTestBase {

  /**
   * The formatter manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatter_manager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $field_manager;

  /**
   * Test API Package.
   *
   * @var \Apigee\Edge\Api\Monetization\Entity\ApiPackageInterface
   */
  protected $api_package;

  /**
   * Test package rate plan.
   *
   * @var \Drupal\apigee_m10n\Entity\RatePlanInterface
   */
  protected $package_rate_plan;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setUp() {
    parent::setUp();

    $this->formatter_manager = $this->container->get('plugin.manager.field.formatter');
    $this->field_manager = $this->container->get('entity_field.manager');

    $this->api_package = $this->createPackage();
    $this->package_rate_plan = $this->createPackageRatePlan($this->api_package);
  }

  /**
   * Test viewing an API Package.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testView() {
    $item_list = $this->package_rate_plan->get('organization');
    static::assertInstanceOf(FieldItemList::class, $item_list);
    static::assertInstanceOf(OrganizationFieldItem::class, $item_list->get(0));
    static::assertSame($this->package_rate_plan->getOrganization()->id(), $item_list->get(0)->value->id());
    /** @var \Drupal\apigee_m10n\Plugin\Field\FieldFormatter\OrganizationFormatter $instance */
    $instance = $this->formatter_manager->createInstance('apigee_organization', [
      'field_definition' => $this->field_manager->getBaseFieldDefinitions('rate_plan')['organization'],
      'settings' => [],
      'label' => TRUE,
      'view_mode' => 'default',
      'third_party_settings' => [],
    ]);
    static::assertInstanceOf(OrganizationFormatter::class, $instance);

    // Render the field item.
    $build = $instance->view($item_list);

    static::assertSame('Organization', (string) $build['#title']);
    static::assertTrue($build['#label_display']);
    static::assertSame($this->package_rate_plan->getOrganization()->getName(), (string) $build[0]['#markup']);

    $this->render($build);
    $this->assertText($this->api_package->getOrganization()->getName());
  }

}
