<?php
/**
 * Used mobile device health report renderer.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field slot definitions for the health report layout.
 *
 * @return array<string, array<string, mixed>>
 */
function almasland_health_report_field_slots() {
	return array(
		'overall_score'       => array(
			'group'  => 'meta',
			'keys'   => array( 'overall_score', 'device_health_score', 'health_score' ),
			'labels' => array( 'امتیاز سلامت دستگاه', 'امتیاز سلامت' ),
		),
		'appearance'          => array(
			'group'  => 'key',
			'icon'   => 'phone',
			'keys'   => array( 'appearance', 'appearance_status', 'visual_condition', 'cosmetic_condition' ),
			'labels' => array( 'وضعیت ظاهری' ),
		),
		'technical_health'    => array(
			'group'  => 'key',
			'icon'   => 'shield-check',
			'keys'   => array( 'technical_health', 'technical_condition', 'functional_health' ),
			'labels' => array( 'سلامت فنی' ),
		),
		'battery_health'      => array(
			'group'  => 'key',
			'icon'   => 'battery',
			'keys'   => array( 'battery_health', 'battery' ),
			'labels' => array( 'سلامت باتری' ),
		),
		'display'             => array(
			'group'  => 'key',
			'icon'   => 'display',
			'keys'   => array( 'display', 'screen', 'display_screen' ),
			'labels' => array( 'صفحه نمایش', 'صفحه‌نمایش' ),
		),
		'repair_history'      => array(
			'group'  => 'key',
			'icon'   => 'wrench',
			'keys'   => array( 'repair_history', 'repair' ),
			'labels' => array( 'سابقه تعمیر' ),
		),
		'registry'            => array(
			'group'  => 'key',
			'icon'   => 'shield',
			'keys'   => array( 'registry', 'registry_status', 'registration' ),
			'labels' => array( 'وضعیت رجیستری', 'رجیستری', 'وضعیت رجستری' ),
		),
		'touch'               => array(
			'group'  => 'secondary',
			'keys'   => array( 'touch', 'touch_health' ),
			'labels' => array( 'سلامت تاچ' ),
		),
		'biometrics'          => array(
			'group'  => 'secondary',
			'keys'   => array( 'biometrics', 'face_id', 'touch_id', 'face_touch_id' ),
			'labels' => array( 'Face ID / Touch ID', 'Face ID', 'Touch ID', 'FaceID / TouchID', 'فیس آیدی', 'تاچ آیدی' ),
		),
		'camera'              => array(
			'group'  => 'secondary',
			'keys'   => array( 'camera', 'cameras' ),
			'labels' => array( 'دوربین جلو و عقب', 'دوربین' ),
		),
		'charging_port'       => array(
			'group'  => 'secondary',
			'keys'   => array( 'charging_port', 'charge_port', 'port_health', 'charging_port_health' ),
			'labels' => array( 'سلامت پورت شارژ', 'پورت شارژ', 'سلامت پورت', 'پورت شارژر' ),
		),
		'connectivity'        => array(
			'group'  => 'secondary',
			'keys'   => array( 'connectivity', 'wifi_bluetooth_gps', 'wifi', 'bluetooth', 'gps' ),
			'labels' => array( 'Wi-Fi / Bluetooth / GPS', 'WiFi / Bluetooth / GPS', 'WIFI / Bluetooth / GPS', 'وای‌فای', 'بلوتوث' ),
		),
		'battery_replacement' => array(
			'group'  => 'secondary',
			'keys'   => array( 'battery_replacement', 'battery_replaced' ),
			'labels' => array( 'سابقه تعویض باتری' ),
		),
		'accessories'         => array(
			'group'  => 'secondary',
			'keys'   => array( 'accessories', 'included_items' ),
			'labels' => array( 'اقلام همراه' ),
		),
	);
}

/**
 * Normalize a label for fuzzy matching.
 *
 * @param string $label Label.
 * @return string
 */
function almasland_health_report_normalize_label( $label ) {
	$label = preg_replace( '/\s+/u', '', mb_strtolower( trim( wp_strip_all_tags( (string) $label ) ) ) );
	$label = str_replace( array( '‌', 'ي', 'ك' ), array( '', 'ی', 'ک' ), $label );

	return (string) $label;
}

/**
 * Whether a field label matches configured patterns.
 *
 * @param string   $label    Field label.
 * @param string[] $patterns Patterns.
 * @return bool
 */
function almasland_health_report_label_matches( $label, array $patterns ) {
	$normalized = almasland_health_report_normalize_label( $label );

	if ( '' === $normalized ) {
		return false;
	}

	foreach ( $patterns as $pattern ) {
		$pattern = almasland_health_report_normalize_label( $pattern );

		if ( '' === $pattern ) {
			continue;
		}

		if ( $normalized === $pattern || str_contains( $normalized, $pattern ) || str_contains( $pattern, $normalized ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Resolve a field to a slot id.
 *
 * @param array<string, mixed> $field Field schema.
 * @return string
 */
function almasland_health_report_resolve_slot( array $field ) {
	$key   = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
	$label = isset( $field['label'] ) ? (string) $field['label'] : '';

	foreach ( almasland_health_report_field_slots() as $slot_id => $slot ) {
		if ( $key && in_array( $key, $slot['keys'], true ) ) {
			return $slot_id;
		}

		if ( $label && almasland_health_report_label_matches( $label, $slot['labels'] ) ) {
			return $slot_id;
		}
	}

	/**
	 * Fuzzy fallback for APSB auto-generated keys and slightly different labels.
	 */
	$normalized_label = almasland_health_report_normalize_label( $label );

	if ( '' !== $normalized_label ) {
		$keyword_map = array(
			'appearance'          => array( 'وضعیتظاهری' ),
			'technical_health'    => array( 'سلامتفنی' ),
			'battery_health'      => array( 'سلامتباتری' ),
			'display'             => array( 'صفحنمایش', 'صفحهنمایش' ),
			'repair_history'      => array( 'سابقهتعمیر' ),
			'registry'            => array( 'وضعیترجیستری', 'رجیست' ),
			'touch'               => array( 'سلامتتاچ' ),
			'biometrics'          => array( 'faceid/touchid', 'faceid', 'touchid' ),
			'camera'              => array( 'دوربینجلووعقب', 'دوربین' ),
			'charging_port'       => array( 'سلامتپورتشارژ', 'پورتشارژ' ),
			'connectivity'        => array( 'wi-fi/bluetooth/gps', 'wifibluetoothgps' ),
			'battery_replacement' => array( 'سابقهتعویضباتری', 'تعویضباتری' ),
			'accessories'         => array( 'اقلامهمراه' ),
			'overall_score'       => array( 'امتیازسلامتدستگاه', 'امتیازسلامت' ),
		);

		foreach ( $keyword_map as $slot_id => $keywords ) {
			foreach ( $keywords as $keyword ) {
				$keyword = almasland_health_report_normalize_label( $keyword );

				if ( '' !== $keyword && str_contains( $normalized_label, $keyword ) ) {
					return $slot_id;
				}
			}
		}
	}

	return '';
}

/**
 * Get APSB field registry when available.
 *
 * @return \APSB\Fields\FieldRegistry|null
 */
function almasland_health_report_field_registry() {
	if ( ! class_exists( '\APSB\Core\Plugin' ) ) {
		return null;
	}

	try {
		return \APSB\Core\Plugin::instance()
			->container()
			->get( \APSB\Fields\FieldRegistry::class );
	} catch ( \Throwable $exception ) {
		return null;
	}
}

/**
 * Whether a raw field value should be treated as empty.
 *
 * @param mixed $value Value.
 * @return bool
 */
function almasland_health_report_is_empty_value( $value ) {
	$registry = almasland_health_report_field_registry();

	if ( $registry ) {
		return $registry->is_empty_value( $value );
	}

	if ( is_array( $value ) ) {
		return empty( array_filter( $value, static function ( $item ) {
			return null !== $item && '' !== trim( (string) $item );
		} ) );
	}

	return null === $value || '' === trim( (string) $value );
}

/**
 * Convert a raw APSB value to plain text.
 *
 * @param array<string, mixed> $field Field schema.
 * @param mixed                $value Raw value.
 * @return string
 */
function almasland_health_report_plain_value( array $field, $value ) {
	$registry = almasland_health_report_field_registry();

	if ( $registry ) {
		return trim( wp_strip_all_tags( $registry->stringify_value( $field, $value ) ) );
	}

	if ( is_bool( $value ) ) {
		return $value ? __( 'بله', 'almas-land' ) : __( 'خیر', 'almas-land' );
	}

	if ( is_numeric( $value ) ) {
		return trim( (string) $value );
	}

	if ( is_array( $value ) ) {
		$value = implode( '، ', array_map( 'strval', $value ) );
	}

	$value = trim( wp_strip_all_tags( (string) $value ) );

	if ( '' === $value ) {
		return '';
	}

	if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
		foreach ( $field['options'] as $option ) {
			if ( ! is_array( $option ) || ! isset( $option['value'] ) ) {
				continue;
			}

			if ( (string) $option['value'] === (string) $value ) {
				return trim( (string) ( $option['label'] ?? $value ) );
			}
		}
	}

	return $value;
}

/**
 * Parse appearance grade and note.
 *
 * @param string $value Value.
 * @return array{grade: string, note: string}
 */
function almasland_health_report_parse_appearance( $value ) {
	$plain = trim( wp_strip_all_tags( (string) $value ) );
	$grade = '';
	$note  = '';

	if ( preg_match( '/(\+{1,2}[A-Z]|[A-Z]\+{0,2})/u', $plain, $matches ) ) {
		$grade = $matches[1];
	} elseif ( preg_match( '/درجه\s*([A-Z\+]+)/ui', $plain, $matches ) ) {
		$grade = trim( $matches[1] );
	}

	if ( preg_match( '/\(([^)]+)\)/u', $plain, $matches ) ) {
		$note = trim( $matches[1] );
	} elseif ( $grade ) {
		$remainder = trim( str_replace( $grade, '', $plain ) );
		$remainder = trim( preg_replace( '/^[(\s\-—–]+|[)\s]+$/u', '', $remainder ) );

		if ( $remainder && $remainder !== $plain ) {
			$note = $remainder;
		}
	}

	if ( '' === $grade ) {
		$grade = $plain;
	}

	return array(
		'grade' => $grade,
		'note'  => $note,
	);
}

/**
 * Extract a percentage from a value.
 *
 * @param string $value Value.
 * @return int|null
 */
function almasland_health_report_parse_percent( $value ) {
	$plain = trim( wp_strip_all_tags( (string) $value ) );

	if ( preg_match( '/(\d{1,3})/u', $plain, $matches ) ) {
		return min( 100, max( 0, (int) $matches[1] ) );
	}

	return null;
}

/**
 * Determine badge tone for a value.
 *
 * @param string $value Value.
 * @param string $slot  Slot id.
 * @return string
 */
function almasland_health_report_value_tone( $value, $slot = '' ) {
	$plain = mb_strtolower( trim( wp_strip_all_tags( (string) $value ) ) );

	if ( in_array( $slot, array( 'accessories', 'battery_replacement' ), true ) ) {
		return 'neutral';
	}

	$positive_patterns = array(
		'سالم',
		'تست شده',
		'رجیستر',
		'آکبند',
		'بدون تعمیر',
		'بدون خط',
		'بدون هیچ',
	);

	foreach ( $positive_patterns as $pattern ) {
		if ( str_contains( $plain, $pattern ) ) {
			return 'positive';
		}
	}

	return 'neutral';
}

/**
 * Build structured health report data from APSB payload.
 *
 * @param WC_Product $product Product.
 * @return array<string, mixed>|null
 */
function almasland_get_used_device_health_report_data( $product ) {
	if ( ! $product instanceof WC_Product || ! function_exists( 'apsb_get_product_specs' ) ) {
		return null;
	}

	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
	$payload    = apsb_get_product_specs( $product_id );

	if ( empty( $payload['fields'] ) || ! is_array( $payload['fields'] ) ) {
		return null;
	}

	$values = isset( $payload['values'] ) && is_array( $payload['values'] ) ? $payload['values'] : array();
	$items  = array();

	foreach ( $payload['fields'] as $field ) {
		if ( ! is_array( $field ) || empty( $field['key'] ) ) {
			continue;
		}

		$type = isset( $field['type'] ) ? (string) $field['type'] : 'text';

		if ( in_array( $type, array( 'heading', 'html', 'divider', 'tab', 'accordion' ), true ) ) {
			continue;
		}

		$key   = (string) $field['key'];
		$value = isset( $values[ $key ] ) ? $values[ $key ] : ( $field['default'] ?? '' );

		if ( almasland_health_report_is_empty_value( $value ) ) {
			continue;
		}

		$plain = almasland_health_report_plain_value( $field, $value );

		if ( '' === $plain ) {
			continue;
		}

		$slot_id = almasland_health_report_resolve_slot( $field );

		$items[] = array(
			'key'   => $key,
			'label' => isset( $field['label'] ) ? (string) $field['label'] : $key,
			'value' => $plain,
			'slot'  => $slot_id,
			'tone'  => almasland_health_report_value_tone( $plain, $slot_id ),
		);
	}

	if ( empty( $items ) ) {
		return null;
	}

	return array(
		'items' => $items,
	);
}

/**
 * Assign report items to header, key grid, and secondary sections.
 *
 * @param array<int, array<string, mixed>> $items Report items.
 * @return array<string, mixed>
 */
function almasland_health_report_assign_sections( array $items ) {
	$key_order       = array( 'appearance', 'technical_health', 'battery_health', 'display', 'repair_history', 'registry' );
	$secondary_order = array( 'touch', 'biometrics', 'camera', 'charging_port', 'connectivity', 'battery_replacement', 'accessories' );

	$placements         = array();
	$claimed_key_slots  = array();
	$claimed_meta_slots = array();
	$claimed_secondary  = array();

	foreach ( $items as $item ) {
		$slot = isset( $item['slot'] ) ? (string) $item['slot'] : '';
		$key  = (string) $item['key'];

		if ( 'overall_score' === $slot && ! isset( $claimed_meta_slots['overall_score'] ) ) {
			$placements[ $key ]           = 'header';
			$claimed_meta_slots['overall_score'] = true;
			continue;
		}

		if ( $slot && in_array( $slot, $key_order, true ) && ! isset( $claimed_key_slots[ $slot ] ) ) {
			$placements[ $key ]           = 'key';
			$claimed_key_slots[ $slot ] = true;
			continue;
		}

		if ( $slot && in_array( $slot, $secondary_order, true ) && ! isset( $claimed_secondary[ $slot ] ) ) {
			$placements[ $key ]        = 'secondary';
			$claimed_secondary[ $slot ] = true;
			continue;
		}

		$placements[ $key ] = 'secondary';
	}

	$key_items           = array();
	$secondary_items     = array();
	$overall_score_item  = null;
	$appearance_item     = null;

	foreach ( $items as $item ) {
		$key        = (string) $item['key'];
		$placement  = $placements[ $key ] ?? 'secondary';
		$slot       = isset( $item['slot'] ) ? (string) $item['slot'] : '';

		if ( 'header' === $placement ) {
			$overall_score_item = $item;
			continue;
		}

		if ( 'key' === $placement ) {
			$key_items[ $slot ] = $item;

			if ( 'appearance' === $slot ) {
				$appearance_item = $item;
			}
			continue;
		}

		$secondary_items[] = $item;
	}

	usort(
		$secondary_items,
		static function ( $left, $right ) use ( $secondary_order ) {
			$left_slot  = isset( $left['slot'] ) ? (string) $left['slot'] : '';
			$right_slot = isset( $right['slot'] ) ? (string) $right['slot'] : '';
			$left_index = array_search( $left_slot, $secondary_order, true );
			$right_index = array_search( $right_slot, $secondary_order, true );

			if ( false === $left_index && false === $right_index ) {
				return 0;
			}

			if ( false === $left_index ) {
				return 1;
			}

			if ( false === $right_index ) {
				return -1;
			}

			return $left_index <=> $right_index;
		}
	);

	$key_items_ordered = array();

	foreach ( $key_order as $slot_id ) {
		if ( ! empty( $key_items[ $slot_id ] ) ) {
			$key_items_ordered[ $slot_id ] = $key_items[ $slot_id ];
		}
	}

	return array(
		'key_items'          => $key_items_ordered,
		'secondary_items'    => $secondary_items,
		'overall_score_item' => $overall_score_item,
		'appearance_item'    => $appearance_item,
	);
}

/**
 * Render an inline SVG icon for the health report.
 *
 * @param string $icon Icon id.
 * @return string
 */
function almasland_health_report_icon( $icon ) {
	$icons = array(
		'phone'         => '<path d="M8 3h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M11 18h2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
		'shield-check'  => '<path d="M12 3 19 6v6c0 4.2-2.8 7.4-7 9-4.2-1.6-7-4.8-7-9V6l7-3Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'battery'       => '<rect x="4" y="7" width="16" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M20 10v4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7 10h8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
		'display'       => '<rect x="3" y="5" width="18" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M8 21h8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
		'wrench'        => '<path d="m14.7 6.3 3 3a3 3 0 0 1-4.2 4.2l-6.4 6.4-2.8-.7-.7-2.8 6.4-6.4a3 3 0 0 1 4.2-4.2Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'shield'        => '<path d="M12 3 19 6v6c0 4.2-2.8 7.4-7 9-4.2-1.6-7-4.8-7-9V6l7-3Z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'chevron-down'  => '<path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
		'check-circle'  => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m8.5 12.2 2.2 2.2 4.8-4.8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
	);

	if ( ! isset( $icons[ $icon ] ) ) {
		return '';
	}

	return '<svg class="device-health-report__icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $icon ] . '</svg>';
}

/**
 * Render a status badge.
 *
 * @param string $value Value.
 * @param string $tone  Badge tone.
 * @return string
 */
function almasland_health_report_render_badge( $value, $tone = 'neutral' ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );

	if ( '' === $value ) {
		return '';
	}

	$prefix = 'positive' === $tone ? '<span class="device-health-report__badge-mark" aria-hidden="true">✓</span>' : '';

	return sprintf(
		'<span class="device-health-report__badge device-health-report__badge--%1$s"><span class="device-health-report__badge-text">%2$s%3$s</span></span>',
		esc_attr( $tone ),
		$prefix,
		esc_html( almasland_persian_digits( $value ) )
	);
}

/**
 * Render a key metric card.
 *
 * @param array<string, mixed> $item Item data.
 * @param string             $slot Slot id.
 * @return void
 */
function almasland_health_report_render_key_card( array $item, $slot ) {
	$slots   = almasland_health_report_field_slots();
	$icon    = isset( $slots[ $slot ]['icon'] ) ? (string) $slots[ $slot ]['icon'] : '';
	$label   = $item['label'];
	$value   = $item['value'];
	$tone    = $item['tone'];
	$primary = '';
	$meta    = '';
	$extra   = '';

	switch ( $slot ) {
		case 'appearance':
			$parsed  = almasland_health_report_parse_appearance( $value );
			$primary = $parsed['grade'];
			$meta    = $parsed['note'];
			break;

		case 'technical_health':
			$percent = almasland_health_report_parse_percent( $value );
			$primary = null !== $percent ? almasland_persian_digits( (string) $percent ) . '%' : almasland_persian_digits( $value );

			if ( str_contains( mb_strtolower( $value ), 'تست' ) ) {
				$status_text = trim( preg_replace( '/\d+\s*%?/u', '', $value ) );
				$status_text = trim( preg_replace( '/^[:\-\s]+|[:\-\s]+$/u', '', $status_text ) );
				$extra       = almasland_health_report_render_badge( '' !== $status_text ? $status_text : __( 'تست شده', 'almas-land' ), 'positive' );
			}
			break;

		case 'battery_health':
			$percent = almasland_health_report_parse_percent( $value );
			$primary = null !== $percent ? almasland_persian_digits( (string) $percent ) . '%' : almasland_persian_digits( $value );

			if ( null !== $percent ) {
				$extra = sprintf(
					'<span class="device-health-report__battery" role="img" aria-label="%1$s"><span class="device-health-report__battery-track"><span class="device-health-report__battery-fill" style="width:%2$d%%"></span></span></span>',
					esc_attr(
						sprintf(
							/* translators: %d: battery health percentage */
							__( 'سلامت باتری %d درصد', 'almas-land' ),
							$percent
						)
					),
					$percent
				);
			}
			break;

		case 'display':
		case 'repair_history':
		case 'registry':
			if ( 'positive' === $tone ) {
				$extra = almasland_health_report_render_badge( $value, 'positive' );
			} else {
				$primary = almasland_persian_digits( $value );
			}
			break;

		default:
			$primary = almasland_persian_digits( $value );
			break;
	}

	if ( '' === $primary && '' === $extra ) {
		$primary = almasland_persian_digits( $value );
	}
	?>
	<article class="device-health-report__key-card device-health-report__key-card--<?php echo esc_attr( $slot ); ?>">
		<div class="device-health-report__key-head">
			<?php if ( $icon ) : ?>
				<span class="device-health-report__key-icon"><?php echo almasland_health_report_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
			<span class="device-health-report__key-label"><?php echo esc_html( $label ); ?></span>
		</div>
		<div class="device-health-report__key-body">
			<?php if ( '' !== $primary ) : ?>
				<strong class="device-health-report__key-value<?php echo 'appearance' === $slot ? ' device-health-report__key-value--grade' : ''; ?>"><?php echo esc_html( $primary ); ?></strong>
			<?php endif; ?>
			<?php if ( '' !== $meta ) : ?>
				<span class="device-health-report__key-meta"><?php echo esc_html( almasland_persian_digits( $meta ) ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $extra ) : ?>
				<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Render used device health report section.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function almasland_render_used_device_health_report( $product ) {
	if ( ! $product instanceof WC_Product || ! almasland_is_used_product( $product ) ) {
		return;
	}

	$report = almasland_get_used_device_health_report_data( $product );

	if ( null === $report || empty( $report['items'] ) ) {
		return;
	}

	$sections        = almasland_health_report_assign_sections( $report['items'] );
	$key_items       = $sections['key_items'];
	$secondary_items = $sections['secondary_items'];
	$appearance_item = $sections['appearance_item'];
	$overall_item    = $sections['overall_score_item'];

	$appearance_grade = '';
	$appearance_note  = '';

	if ( ! empty( $appearance_item ) ) {
		$appearance       = almasland_health_report_parse_appearance( $appearance_item['value'] );
		$appearance_grade = $appearance['grade'];
		$appearance_note  = $appearance['note'];
	}

	$score_value = null;

	if ( ! empty( $overall_item ) ) {
		$score_value = almasland_health_report_parse_percent( $overall_item['value'] );

		if ( null === $score_value ) {
			$score_value = almasland_health_report_parse_percent( preg_replace( '/\D+/u', '', $overall_item['value'] ) );
		}
	}
	?>
	<section class="product-info-apsb device-health-report" aria-labelledby="used-product-specs-title" dir="rtl">
		<header class="device-health-report__header">
			<div class="device-health-report__intro">
				<h2 id="used-product-specs-title"><?php esc_html_e( 'گزارش وضعیت و سلامت دستگاه', 'almas-land' ); ?></h2>
				<p><?php esc_html_e( 'دستگاه توسط کارشناسان الماس لند بررسی و تست شده است.', 'almas-land' ); ?></p>
			</div>

			<?php if ( $appearance_grade || null !== $score_value ) : ?>
				<div class="device-health-report__summary">
					<?php if ( $appearance_grade ) : ?>
						<div class="device-health-report__summary-item device-health-report__summary-item--grade">
							<span class="device-health-report__summary-label"><?php esc_html_e( 'وضعیت کلی', 'almas-land' ); ?></span>
							<strong class="device-health-report__summary-grade"><?php echo esc_html( $appearance_grade ); ?></strong>
							<?php if ( $appearance_note ) : ?>
								<span class="device-health-report__summary-note"><?php echo esc_html( almasland_persian_digits( $appearance_note ) ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( null !== $score_value ) : ?>
						<div class="device-health-report__summary-item device-health-report__summary-item--score" aria-label="<?php echo esc_attr( sprintf( __( 'امتیاز سلامت دستگاه %d از 100', 'almas-land' ), $score_value ) ); ?>">
							<div class="device-health-report__score-value">
								<strong><?php echo esc_html( almasland_persian_digits( (string) $score_value ) ); ?></strong>
								<span class="device-health-report__score-max"><?php echo esc_html( almasland_persian_digits( '/ 100' ) ); ?></span>
							</div>
							<span class="device-health-report__score-label"><?php esc_html_e( 'امتیاز سلامت دستگاه', 'almas-land' ); ?></span>
						</div>
					<?php elseif ( ! empty( $overall_item ) ) : ?>
						<div class="device-health-report__summary-item device-health-report__summary-item--score">
							<div class="device-health-report__score-value">
								<strong><?php echo esc_html( almasland_persian_digits( $overall_item['value'] ) ); ?></strong>
							</div>
							<span class="device-health-report__score-label"><?php echo esc_html( $overall_item['label'] ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $key_items ) ) : ?>
			<div class="device-health-report__key-grid">
				<?php foreach ( $key_items as $slot_id => $item ) : ?>
					<?php almasland_health_report_render_key_card( $item, $slot_id ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $secondary_items ) ) : ?>
			<section class="device-health-report__details" aria-label="<?php esc_attr_e( 'سایر جزئیات تست و سلامت', 'almas-land' ); ?>">
				<h3 class="device-health-report__details-title"><?php esc_html_e( 'سایر جزئیات تست و سلامت', 'almas-land' ); ?></h3>
				<div class="device-health-report__details-grid">
					<?php foreach ( $secondary_items as $item ) : ?>
						<div class="device-health-report__detail-item">
							<span class="device-health-report__detail-label"><?php echo esc_html( $item['label'] ); ?></span>
							<div class="device-health-report__detail-value">
								<?php
								if ( 'positive' === $item['tone'] ) {
									echo almasland_health_report_render_badge( $item['value'], 'positive' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo almasland_health_report_render_badge( $item['value'], 'neutral' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<footer class="device-health-report__trust">
			<span class="device-health-report__trust-icon"><?php echo almasland_health_report_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div class="device-health-report__trust-copy">
				<h3><?php esc_html_e( 'تست و بررسی شده توسط کارشناسان الماس لند', 'almas-land' ); ?></h3>
				<p><?php esc_html_e( 'ما به شفافیت در فروش اعتماد داریم. هر دستگاه قبل از فروش به صورت کامل بررسی و تست می‌شود.', 'almas-land' ); ?></p>
			</div>
		</footer>
	</section>
	<?php
}

/**
 * Whether the product has APSB health report data.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function almasland_product_has_used_health_report( $product ) {
	if ( ! $product instanceof WC_Product || ! almasland_is_used_product( $product ) ) {
		return false;
	}

	return null !== almasland_get_used_device_health_report_data( $product );
}

/**
 * Get buy reasons for used product gallery panel.
 *
 * @param WC_Product $product Product.
 * @return string[]
 */
function almasland_get_used_product_buy_reasons( $product ) {
	$reasons = array(
		__( 'وضعیت ظاهری بررسی شده', 'almas-land' ),
		__( 'سلامت فنی تست شده', 'almas-land' ),
		__( 'سلامت باتری بررسی شده', 'almas-land' ),
		__( 'دوربین و Face ID تست شده', 'almas-land' ),
		__( 'صفحه‌نمایش تست شده', 'almas-land' ),
		__( 'مهلت تست فروشگاه', 'almas-land' ),
	);

	/**
	 * Filter buy reasons shown under the used product gallery.
	 *
	 * @param string[]   $reasons Reasons.
	 * @param WC_Product $product Product.
	 */
	return apply_filters( 'almasland_used_product_buy_reasons', $reasons, $product );
}

/**
 * Get service highlights for used product gallery panel.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{icon: string, text: string}>
 */
function almasland_get_used_product_service_highlights( $product ) {
	$warranty_text = trim( wp_strip_all_tags( (string) $product->get_attribute( 'guarantee' ) ) );

	$items = array(
		array(
			'icon' => 'truck',
			'text' => __( 'ارسال سریع به سراسر کشور', 'almas-land' ),
		),
		array(
			'icon' => 'shield',
			'text' => __( 'ضمانت سلامت و اصالت کالا', 'almas-land' ),
		),
		array(
			'icon' => 'return',
			'text' => $warranty_text ? $warranty_text : __( 'مهلت تست / شرایط مرجوعی', 'almas-land' ),
		),
		array(
			'icon' => 'box',
			'text' => __( 'بسته‌بندی ایمن', 'almas-land' ),
		),
		array(
			'icon' => 'chat',
			'text' => __( 'امکان مشاوره قبل از خرید', 'almas-land' ),
		),
	);

	/**
	 * Filter service highlights shown under the used product gallery.
	 *
	 * @param array<int, array{icon: string, text: string}> $items Items.
	 * @param WC_Product                                    $product Product.
	 */
	return apply_filters( 'almasland_used_product_service_highlights', $items, $product );
}

/**
 * Render a small service icon for the gallery panel.
 *
 * @param string $icon Icon id.
 * @return string
 */
function almasland_used_gallery_panel_icon( $icon ) {
	$icons = array(
		'truck'  => '<path d="M3 7h11v8H3V7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M14 10h3l2 2v3h-5v-5Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="17" r="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="17" r="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'shield' => '<path d="M12 3 19 6v6c0 4.2-2.8 7.4-7 9-4.2-1.6-7-4.8-7-9V6l7-3Z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'return' => '<path d="M20 8v6a2 2 0 0 1-2 2H8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="m5 11-3-3 3-3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'box'    => '<path d="M4 8 12 4l8 4-8 4-8-4Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M4 8v8l8 4 8-4V8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
		'chat'   => '<path d="M5 6h14a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H9l-4 3v-3H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
	);

	if ( ! isset( $icons[ $icon ] ) ) {
		return '';
	}

	return '<svg class="used-gallery-panel__icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $icon ] . '</svg>';
}

/**
 * Render minimal trust panel below used product gallery.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function almasland_render_used_product_gallery_panel( $product ) {
	if ( ! $product instanceof WC_Product || ! almasland_product_has_used_health_report( $product ) ) {
		return;
	}

	$reasons  = almasland_get_used_product_buy_reasons( $product );
	$services = almasland_get_used_product_service_highlights( $product );
	?>
	<aside class="used-gallery-panel" aria-label="<?php esc_attr_e( 'مزایای خرید این محصول', 'almas-land' ); ?>">
		<section class="used-gallery-panel__benefits">
			<h3 class="used-gallery-panel__title"><?php esc_html_e( 'چرا این محصول را بخریم؟', 'almas-land' ); ?></h3>
			<ul class="used-gallery-panel__list">
				<?php foreach ( $reasons as $reason ) : ?>
					<li class="used-gallery-panel__item">
						<span class="used-gallery-panel__check" aria-hidden="true">✓</span>
						<span><?php echo esc_html( $reason ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<hr class="used-gallery-panel__divider" aria-hidden="true">

		<section class="used-gallery-panel__services" aria-label="<?php esc_attr_e( 'خدمات فروشگاه', 'almas-land' ); ?>">
			<ul class="used-gallery-panel__service-list">
				<?php foreach ( $services as $service ) : ?>
					<li class="used-gallery-panel__service-item">
						<span class="used-gallery-panel__service-icon"><?php echo almasland_used_gallery_panel_icon( $service['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span><?php echo esc_html( $service['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	</aside>
	<?php
}
