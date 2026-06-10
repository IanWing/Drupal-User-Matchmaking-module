<?php

namespace Drupal\user_matchmaking\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the User Match entity.
 *
 * @ContentEntityType(
 *   id = "user_match",
 *   label = @Translation("User Match"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data"   = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add"     = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit"    = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete"  = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   base_table = "user_match",
 *   entity_keys = {
 *     "id"      = "id",
 *     "uuid"    = "uuid",
 *     "changed" = "changed"
 *   }
 * )
 */
class UserMatch extends ContentEntityBase implements EntityChangedInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setReadOnly(TRUE);

        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setReadOnly(TRUE);

        // The user who triggered the match (OFFERER) 
        $fields['offerer_uid'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Offerer'))
            ->setSetting('target_type', 'user')
            ->setRequired(TRUE)
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'entity_reference_label', 'weight' => 0])
            ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 0]);

        // The user who gets notified (SEEKER)
        $fields['seeker_uid'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Seeker'))
            ->setSetting('target_type', 'user')
            ->setRequired(TRUE)
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'entity_reference_label', 'weight' => 1])
            ->setDisplayOptions('form', ['type' => 'entity_reference_autocomplete', 'weight' => 1]);

        //Date when the match was created
        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'timestamp', 'weight' => 2]);

        //Date of last update to the match (also on read)
        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'timestamp', 'weight' => 3]);

        // timestamp of when the notification was sent to the seeker for the last time
        $fields['notified_on'] = BaseFieldDefinition::create('timestamp')
            ->setLabel(t('Notified on'))
            ->setDescription(t('When the notification was last sent.'))
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'timestamp', 'weight' => 5])
            ->setDisplayConfigurable('view', TRUE);

        // Bool of seeker has read the notification about the match
        $fields['read'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Read'))
            ->setDefaultValue(FALSE)
            ->setDisplayOptions('view', ['label' => 'above', 'type' => 'boolean', 'weight' => 4])
            ->setDisplayOptions('form', ['type' => 'boolean_checkbox', 'weight' => 4]);

        // working on matching terms, to be added

        return $fields;
    }
}
