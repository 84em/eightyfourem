<?php

/**
 * Automatically generate and save schema.org structured data for posts, pages, and projects.
 *
 * The `save_post` action is used to hook into post save/update events, ensuring that structured data
 * is generated and saved only for a given set of post types. The schema data follows the guidelines
 * of schema.org and is tailored to specific WordPress post types (post, page, and custom post types like "project").
 *
 * The following high-level operations are performed:
 * - Preventing execution during autosave or post revision updates.
 * - Verifying user permissions to edit the current post.
 * - Checking the post type to determine if schema generation applies (post, page, project).
 * - Generating structured data for supported post types based on schema.org guidelines with specific attributes.
 * - Adding breadcrumb trail information for enhanced schema context.
 * - Providing additional attributes or keywords relevant to the content type, such as author, categories, and tags.
 *
 * For `project` post types:
 * - Keywords are extracted from the content based on predefined technical terms.
 * - The schema includes additional properties such as creator information and genres.
 *
 * For `post` post types:
 * - Adds author and publisher information.
 * - Includes associated categories and tags as keywords.
 *
 * For `page` post types:
 * - Custom handling for specific page slugs like "contact" or "services" to provide more contextual schema.
 *
 * @hook save_post
 *
 * @param  int  $post_id  The ID of the post being saved.
 *
 * @return void This function does not return a value. It halts execution if certain criteria (e.g., autosave, permissions)
 *              are not met, or it proceeds to generate schema and update database as needed.
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_action(
    hook_name: 'wp_after_insert_post',
    callback: function ( $post_id, $post, $update ) {
        // Prevent autosave and revision updates
        if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Check user permissions (skip for WP-CLI)
        if ( ! \defined( 'WP_CLI' ) && ! \current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Get post data
        $post      = \get_post( $post_id );
        $post_type = \get_post_type( $post_id );
        $post_url  = \get_permalink( $post_id );
        $site_url  = \get_site_url();

        // Only generate schema for posts, pages, and projects
        if ( ! \in_array( $post_type, [ 'post', 'page', 'project' ] ) ) {
            return;
        }

        // Base schema structure
        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'WebPage',
            '@id'           => $post_url . '#webpage',
            'url'           => $post_url,
            'name'          => \get_the_title( $post_id ),
            'description'   => \get_post_meta( $post_id, '_genesis_description', true ) ?: \wp_trim_words( \strip_tags( $post->post_content ), 25 ),
            'inLanguage'    => 'en-US',
            'datePublished' => \get_the_date( 'c', $post_id ),
            'dateModified'  => \get_the_modified_date( 'c', $post_id ),
            'isPartOf'      => [
                '@type' => 'WebSite',
                '@id'   => $site_url . '/#website',
                'url'   => $site_url,
                'name'  => '84EM',
            ],
            'breadcrumb'    => [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [],
            ],
        ];

        // Generate breadcrumbs
        $breadcrumbs = [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Home',
                'item'     => $site_url,
            ],
        ];

        // Add post type specific breadcrumb
        if ( $post_type === 'project' ) {
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Projects',
                'item'     => $site_url . '/project/',
            ];
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => 3,
                'name'     => \get_the_title( $post_id ),
                'item'     => $post_url,
            ];
        } elseif ( $post_type === 'post' ) {
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Blog',
                'item'     => \get_permalink( \get_option( 'page_for_posts' ) ),
            ];
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => 3,
                'name'     => \get_the_title( $post_id ),
                'item'     => $post_url,
            ];
        } else {
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => \get_the_title( $post_id ),
                'item'     => $post_url,
            ];
        }

        $schema['breadcrumb']['itemListElement'] = $breadcrumbs;

        // Post type specific schema enhancements
        switch ( $post_type ) {
            case 'project':
                // Use array of types to properly support both WebPage (for breadcrumbs) and CreativeWork
                $schema['@type']   = ['WebPage', 'CreativeWork'];
                $schema['creator'] = [
                    '@type' => 'Organization',
                    '@id'   => $site_url . '/#organization',
                    'name'  => '84EM',
                ];
                $schema['genre']   = 'WordPress Development';

                // Extract keywords from content
                $content  = \strip_tags( $post->post_content );
                $keywords = [];

                // Try to get keywords from 84em-local-pages plugin first
                $tech_keywords = [];
                if ( \class_exists( 'EightyFourEM\LocalPages\Plugin' ) ) {
                    try {
                        $plugin = \EightyFourEM\LocalPages\Plugin::getInstance();
                        $container = $plugin->getContainer();
                        if ( $container->has( 'EightyFourEM\LocalPages\Data\KeywordsProvider' ) ) {
                            $keywordsProvider = $container->get( 'EightyFourEM\LocalPages\Data\KeywordsProvider' );
                            $tech_keywords = $keywordsProvider->getKeys();
                        }
                    } catch ( \Exception $e ) {
                        // Silently fall back to hardcoded list if container fails
                    }
                }

                // Fallback to hardcoded keywords if plugin not available
                if ( empty( $tech_keywords ) ) {
                    $tech_keywords = [
                        'WordPress',
                        'plugin',
                        'API',
                        'WooCommerce',
                        'custom',
                        'integration',
                        'security',
                        'database',
                        'PHP',
                        'JavaScript',
                        'migration',
                        'multisite',
                        'theme',
                        'Gravity Forms',
                        'LearnDash',
                        'financial',
                        'healthcare',
                        'banking',
                        'enterprise',
                        'white label',
                        'API integration',
                        'data migration',
                        'platform migration',
                        'platform transfer',
                        'security audit',
                        'AI',
                        'artificial intelligence',
                        'automation',
                        'maintenance',
                        'support',
                        'consulting',
                        'troubleshooting',
                    ];
                }

                foreach ( $tech_keywords as $keyword ) {
                    if ( \stripos( $content, $keyword ) !== false ) {
                        $keywords[] = $keyword;
                    }
                }

                if ( ! empty( $keywords ) ) {
                    $schema['keywords'] = \implode( ', ', \array_unique( $keywords ) );
                }

                break;

            case 'post':
                $schema['@type']     = 'BlogPosting';
                $schema['author']    = [
                    '@type' => 'Person',
                    'name'  => \get_the_author_meta( 'display_name', $post->post_author ),
                    'url'   => \get_author_posts_url( $post->post_author ),
                ];
                $schema['publisher'] = [
                    '@type' => 'Organization',
                    '@id'   => $site_url . '/#organization',
                    'name'  => '84EM',
                ];

                // Add categories and tags
                $categories = \get_the_category( $post_id );
                if ( ! empty( $categories ) ) {
                    $schema['articleSection'] = $categories[0]->name;
                }

                $tags = \get_the_tags( $post_id );
                if ( ! empty( $tags ) ) {
                    $schema['keywords'] = \implode( ', ', \wp_list_pluck( $tags, 'name' ) );
                }

                break;

            case 'page':
                // Handle specific pages
                $page_slug = $post->post_name;

                switch ( $page_slug ) {
                    case 'contact':
                        $schema['@type']      = 'ContactPage';
                        $schema['mainEntity'] = [
                            '@type'        => 'Organization',
                            '@id'          => $site_url . '/#organization',
                            'name'         => '84EM',
                            'contactPoint' => [
                                '@type'             => 'ContactPoint',
                                'contactType'       => 'customer service',
                                'areaServed'        => 'Worldwide',
                                'availableLanguage' => 'English',
                            ],
                        ];
                        break;

                    case 'services':
                        $schema['mainEntity'] = [
                            '@type'           => 'Organization',
                            '@id'             => $site_url . '/#organization',
                            'hasOfferCatalog' => [
                                '@type'           => 'OfferCatalog',
                                'name'            => 'WordPress Development Services',
                                'itemListElement' => [
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'Custom WordPress Plugin Development',
                                            'url'      => $site_url . '/services/custom-wordpress-plugin-development/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'White Label WordPress Development for Agencies',
                                            'url'      => $site_url . '/services/white-label-wordpress-development-for-agencies/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'WordPress Consulting & Strategy',
                                            'url'      => $site_url . '/services/wordpress-consulting-strategy/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'WordPress Maintenance & Support',
                                            'url'      => $site_url . '/services/wordpress-maintenance-support/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'AI-Enhanced WordPress Development',
                                            'url'      => $site_url . '/services/ai-enhanced-wordpress-development/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'Data Migrations & Platform Transfers',
                                            'url'      => $site_url . '/services/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                    [
                                        '@type'       => 'Offer',
                                        'itemOffered' => [
                                            '@type'    => 'Service',
                                            'name'     => 'Security & Troubleshooting',
                                            'url'      => $site_url . '/services/',
                                            'provider' => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ];
                        break;

                    case 'about':
                        $schema['mainEntity'] = [
                            '@type'      => 'Person',
                            'name'       => 'Andrew Miller',
                            'jobTitle'   => 'WordPress Developer & Consultant',
                            'worksFor'   => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                            ],
                            'knowsAbout' => [
                                'WordPress Plugin Development',
                                'PHP Programming',
                                'API Integration',
                                'WordPress Security',
                                'Data Migration',
                                'Platform Transfers',
                                'AI-Enhanced WordPress Development',
                                'White Label Development',
                                'WordPress Maintenance',
                                'Security Audits',
                                'WordPress Consulting',
                                'Custom WordPress Development',
                            ],
                        ];
                        break;

                    case 'now':
                        $schema['mainEntity'] = [
                            '@type'       => 'ItemList',
                            'name'        => 'Current Development Projects',
                            'description' => 'Projects we\'re actively involved with, updated frequently',
                        ];
                        break;

                    case 'pricing':
                        // Add Service schema with comprehensive pricing information
                        $schema['mainEntity'] = [
                            '@type'            => 'Service',
                            '@id'              => $post_url . '#service',
                            'serviceType'      => 'WordPress Development',
                            'name'             => 'WordPress Development Services',
                            'description'      => 'Professional WordPress development including custom themes, plugins, performance optimization, and maintenance',
                            'provider'         => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'       => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'hasOfferCatalog'  => [
                                '@type'           => 'OfferCatalog',
                                'name'            => 'WordPress Development Services',
                                'itemListElement' => [
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'Custom WordPress Plugin Development',
                                            'description' => 'Custom WordPress plugin development tailored to your specific needs',
                                            'url'         => $site_url . '/services/custom-wordpress-plugin-development/',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '150',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'White Label WordPress Development for Agencies',
                                            'description' => 'White label WordPress development services for agencies and resellers',
                                            'url'         => $site_url . '/services/white-label-wordpress-development-for-agencies/',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '150',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'WordPress Consulting & Strategy',
                                            'description' => 'Expert WordPress consulting and strategic planning services',
                                            'url'         => $site_url . '/services/wordpress-consulting-strategy/',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '150',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'WordPress Maintenance & Support',
                                            'description' => 'Ongoing WordPress maintenance, updates, and technical support',
                                            'url'         => $site_url . '/services/wordpress-maintenance-support/',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '150',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'AI-Enhanced WordPress Development',
                                            'description' => 'WordPress development enhanced with AI tools and automation',
                                            'url'         => $site_url . '/services/ai-enhanced-wordpress-development/',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '150',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                    [
                                        '@type'              => 'Offer',
                                        'itemOffered'        => [
                                            '@type'       => 'Service',
                                            'name'        => 'After-Hours WordPress Development',
                                            'description' => 'After-hours and emergency WordPress development services',
                                            'provider'    => [
                                                '@type' => 'Organization',
                                                '@id'   => $site_url . '/#organization',
                                            ],
                                        ],
                                        'priceSpecification' => [
                                            '@type'        => 'UnitPriceSpecification',
                                            'price'        => '225',
                                            'priceCurrency' => 'USD',
                                            'unitText'     => 'HOUR',
                                        ],
                                        'availability'       => 'https://schema.org/InStock',
                                    ],
                                ],
                            ],
                        ];

                        // Add 'about' property to WebPage schema to reference the Service
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'custom-wordpress-plugin-development':
                        $schema['mainEntity'] = [
                            '@type'              => 'Service',
                            '@id'                => $post_url . '#service',
                            'serviceType'        => 'Custom WordPress Plugin Development',
                            'name'               => 'Custom WordPress Plugin Development',
                            'description'        => 'Custom WordPress plugin development tailored to your specific business requirements, from simple integrations to complex multi-system architectures',
                            'provider'           => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'         => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'offers'             => [
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'Standard Rate',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '150',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'After-Hours Rate',
                                    'description'        => 'After-hours and emergency WordPress development services',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '225',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                            ],
                        ];
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'white-label-wordpress-development-for-agencies':
                        $schema['mainEntity'] = [
                            '@type'              => 'Service',
                            '@id'                => $post_url . '#service',
                            'serviceType'        => 'White Label WordPress Development',
                            'name'               => 'White Label WordPress Development for Agencies',
                            'description'        => 'White label WordPress development services for digital agencies and resellers, providing project-based and ongoing partnership solutions',
                            'provider'           => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'         => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'offers'             => [
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'Standard Rate',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '150',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'After-Hours Rate',
                                    'description'        => 'After-hours and emergency WordPress development services',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '225',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                            ],
                        ];
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'ai-enhanced-wordpress-development':
                        $schema['mainEntity'] = [
                            '@type'              => 'Service',
                            '@id'                => $post_url . '#service',
                            'serviceType'        => 'AI-Enhanced WordPress Development',
                            'name'               => 'AI-Enhanced WordPress Development',
                            'description'        => 'WordPress development enhanced with artificial intelligence tools to deliver solutions faster with better quality at reduced costs',
                            'provider'           => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'         => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'offers'             => [
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'Standard Rate',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '150',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'After-Hours Rate',
                                    'description'        => 'After-hours and emergency WordPress development services',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '225',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                            ],
                        ];
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'wordpress-consulting-strategy':
                        $schema['mainEntity'] = [
                            '@type'              => 'Service',
                            '@id'                => $post_url . '#service',
                            'serviceType'        => 'WordPress Consulting',
                            'name'               => 'WordPress Consulting & Strategy',
                            'description'        => 'Expert WordPress consulting and strategic planning services including technical audits and architecture planning',
                            'provider'           => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'         => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'offers'             => [
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'Standard Rate',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '150',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'After-Hours Rate',
                                    'description'        => 'After-hours and emergency WordPress development services',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '225',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                            ],
                        ];
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'wordpress-maintenance-support':
                        $schema['mainEntity'] = [
                            '@type'              => 'Service',
                            '@id'                => $post_url . '#service',
                            'serviceType'        => 'WordPress Maintenance',
                            'name'               => 'WordPress Maintenance & Support',
                            'description'        => 'Ongoing WordPress maintenance, updates, security monitoring, backups, and troubleshooting to keep your site running reliably',
                            'provider'           => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                                'name'  => '84EM',
                                'url'   => $site_url,
                            ],
                            'areaServed'         => [
                                '@type' => 'Country',
                                'name'  => 'United States',
                            ],
                            'offers'             => [
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'Standard Rate',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '150',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                                [
                                    '@type'              => 'Offer',
                                    'name'               => 'After-Hours Rate',
                                    'description'        => 'After-hours and emergency WordPress development services',
                                    'priceSpecification' => [
                                        '@type'         => 'UnitPriceSpecification',
                                        'price'         => '225',
                                        'priceCurrency' => 'USD',
                                        'unitText'      => 'HOUR',
                                    ],
                                    'availability'       => 'https://schema.org/InStock',
                                ],
                            ],
                        ];
                        $schema['about'] = [
                            '@id' => $post_url . '#service',
                        ];
                        break;

                    case 'privacy-policy':
                        $schema['mainEntity'] = [
                            '@type'       => 'DigitalDocument',
                            'name'        => '84EM Privacy Policy',
                            'description' => 'Privacy policy governing WordPress development services and data protection practices',
                            'author'      => [
                                '@type' => 'Organization',
                                '@id'   => $site_url . '/#organization',
                            ],
                        ];
                        break;

                    case 'testimonials':
                        // Get actual review count from Google Reviews
                        $google_reviews_block = new GoogleReviewsBlock();
                        $google_reviews_data  = $google_reviews_block->get_google_reviews();

                        // Default review count (fallback)
                        $total_review_count = 5;
                        $average_rating     = '5.0';

                        // If Google reviews data is available, use the actual count
                        if ( $google_reviews_data && isset( $google_reviews_data['total_ratings'] ) ) {
                            $total_review_count = $google_reviews_data['total_ratings'];
                            if ( isset( $google_reviews_data['rating'] ) ) {
                                $average_rating = number_format( $google_reviews_data['rating'], 1 );
                            }
                        }

                        // Add the Clutch reviews to the count.  Hard coded value as this information isn't exposed in the page source.
                        $total_review_count += 3;

                        $schema['mainEntity'] = [
                            '@type'           => 'Organization',
                            '@id'             => $site_url . '/#organization',
                            'name'            => '84EM',
                            'description'     => 'Expert WordPress Development Services',
                            'url'             => $site_url,
                            'aggregateRating' => [
                                '@type'       => 'AggregateRating',
                                'ratingValue' => $average_rating,
                                'reviewCount' => (string) $total_review_count,
                                'bestRating'  => '5',
                                'worstRating' => '1',
                            ],
                            'review'          => [
                                [
                                    '@type'         => 'Review',
                                    'reviewRating'  => [
                                        '@type'       => 'Rating',
                                        'ratingValue' => '5',
                                        'bestRating'  => '5',
                                        'worstRating' => '1',
                                    ],
                                    'author'        => [
                                        '@type' => 'Organization',
                                        'name'  => 'Clutch.co Verified Client',
                                    ],
                                    'reviewBody'    => '84EM is nothing but a breath of fresh air in a world of unreliable vendors.',
                                    'datePublished' => \get_the_date( 'c', $post_id ),
                                ],
                            ],
                        ];
                        break;
                }
                break;
        }

        // Add featured image if available
        $featured_image_id = \get_post_thumbnail_id( $post_id );
        if ( $featured_image_id ) {
            $image_url  = \wp_get_attachment_image_url( $featured_image_id, 'full' );
            $image_meta = \wp_get_attachment_metadata( $featured_image_id );

            $schema['image'] = [
                '@type'  => 'ImageObject',
                'url'    => $image_url,
                'width'  => $image_meta['width'] ?? 1200,
                'height' => $image_meta['height'] ?? 630,
            ];
        }

        // Convert to JSON and save to post meta
        $schema_json = \wp_json_encode( $schema, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE );
        \update_post_meta( $post_id, 'schema', $schema_json );
    },
    priority: 99,
    accepted_args: 3 );

// Function to output schema in head
\add_action(
    hook_name: 'wp_head',
    callback: function () {
        if ( \is_singular() ) {
            $schema_json = \get_post_meta( \get_the_ID(), 'schema', true );
            if ( ! empty( $schema_json ) ) {
                // Escape potential </script> tags in JSON to prevent script injection
                echo sprintf(
                    '<script type="application/ld+json">%s</script>',
                    str_replace( '</script>', '<\/script>', $schema_json )
                ) . "\n";
            }
        }
    } );
