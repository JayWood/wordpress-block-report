<?php

namespace plugish\com\cli;

use WP_CLI\Formatter;

class BlockReport {

	/**
	 * @var bool The CSV flag.
	 */
	private bool $csv = false;

	/**
	 * Stores block data.
	 * @var array
	 */
	private array $blocks = [];

	/**
	 * @var array The post types to query for.
	 */
	private array $post_types = [];

	/**
	 * @var int The post count in the query.
	 */
	private int $post_count = 0;

	/**
	 * @var array The post status to check.
	 */
	private array $post_status = [];

	/**
	 * @param array $args The arguments.
	 * @param array $assoc_args Flags/associative arguments.
	 *
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ) {
		$this->csv = isset( $assoc_args['csv'] );
		$this->post_types = ! empty( $assoc_args['post-type'] ) ? explode(',', $assoc_args['post-type'] ) : [ 'post', 'page' ];
		$this->post_status = ! empty( $assoc_args['post-status'] ) ? explode(',', $assoc_args['post-status'] ) : ['any'];

		if ( empty( $assoc_args['fields'] ) ) {
			\WP_CLI::error( 'You cannot list empty fields, why run the command then?' );
		}

		$page = 1;
		$posts = $this->query_posts( $page );
		while( ! empty( $posts ) ) {
			foreach( $posts as $post ) {
				$this->parse_posts( $post );
			}

			$page++;
			$this->cleanup();
			$posts = $this->query_posts( $page );
		}

		$fields = ! empty( $assoc_args['fields'] ) ? explode( ',', $assoc_args['fields'] ) : [];

		$args = [
			'format' => $this->csv ? 'csv' : 'table',
			'fields' => array_map( 'trim', $fields ),
		];

		$formatter = new Formatter( $args );
		$formatter->display_items( $this->blocks );
	}

	/**
	 * Queries the posts for a loop.
	 *
	 * @param int $page The page.
	 *
	 * @return array
	 */
	private function query_posts( int $page = 1 ): array {
		$post_query = new \WP_Query( [
			'post_type' => $this->post_types,
			'post_status' => $this->post_status,
			'posts_per_page' => 100,
			'paged' => $page,
		] );

		if ( ! $post_query->have_posts() ) {
			return [];
		}

		if ( ! $this->post_count ) {
			$this->post_count = absint( $post_query->found_posts );
		}

		return $post_query->posts;
	}

	/**
	 * Parses a blocks array and adds it to the block report.
	 *
	 * @param array $blocks
	 * @param int $post_id
	 *
	 * @return void
	 */
	private function parse_blocks( array $blocks, int $post_id ): void {
		foreach( $blocks as $block ) {
			$parsed = $this->parse_block( $block, $post_id );
			if ( empty( $parsed ) ) {
				continue;
			}

			$this->blocks[] = $parsed;
		}
	}

	/**
	 * Parses a block and returns the resulting array.
	 *
	 * @param array $block
	 * @param int $post_id
	 *
	 * @return array|null
	 */
	private function parse_block( array $block, int $post_id ): ?array {
		if ( empty( $block['blockName'] ) ) {
			return null;
		}

		$innerBlockList = [];
		if ( ! empty( $block['innerBlocks'] ) ) {
			$innerBlockList = wp_list_pluck( $block['innerBlocks'], 'blockName' );
			$this->parse_blocks( $block['innerBlocks'], $post_id );
		}

		return [
			'post_id'      => $post_id,
			'name'         => $block['blockName'],
			'attributes'   => ! empty( $block['attrs'] )
				? implode( ', ', array_keys( $block['attrs'] ) )
				: '-',
			'innerHtml'    =>  ! empty( $block['innerHTML'] ) ? 'yes' : '-',
			'innerContent' =>  ! empty( $block['innerContent'] ) ? 'yes' : '-',
			'innerBlocks'  => ! empty( $innerBlockList )
				? implode( ', ', $innerBlockList )
				: '-',
		];
	}

	/**
	 * Parses posts
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	private function parse_posts( \WP_Post $post ): void {
		$blocks = parse_blocks( $post->post_content );
		if ( empty( $blocks ) ) {
			return;
		}

		$this->parse_blocks( $blocks, $post->ID );
	}

	/**
	 * Cleans up memory after every operation.
	 *
	 * Borrowed from WP VIP
	 *
	 * @link https://github.com/Automattic/vip-go-mu-plugins/blob/develop/vip-helpers/vip-caching.php#L733
	 *
	 * @return void
	 */
	private function cleanup(): void {
		global $wp_object_cache, $wpdb;

		$wpdb->queries = [];
		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops      = [];
		$wp_object_cache->memcache_debug = [];
		$wp_object_cache->cache          = [];

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}
	}
}

\WP_CLI::add_command( 'jwcli block report', __NAMESPACE__ . '\BlockReport', [
	'shortdesc' => 'Scrapes post content for blocks and provides a report on screen or in CSV format.',
	'synopsis' => [
		[
			'type' => 'assoc',
			'name' => 'post-type',
			'description' => 'The post type slugs to check, separated by commas.',
			'optional' => true,
			'default' => 'post,page',
		],
		[
			'type' => 'assoc',
			'name' => 'fields',
			'description' => 'A comma-separated list of fields to return.',
			'default' => 'post_id,name,attributes,innerHtml,innerContent,innerBlocks',
			'optional' => true,
		],
		[
			'type' => 'assoc',
			'name' => 'post-status',
			'description' => 'The post statuses to check.',
			'optional' => true,
			'default' => 'any',
		],
		[
			'type' => 'flag',
			'description' => 'Outputs the CSV data to STDOUT.',
			'name' => 'csv',
			'optional' => true,
		],
	],
] );