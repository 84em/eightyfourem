(function(blocks, element, blockEditor, components, i18n, data) {
	const { registerBlockType } = blocks;
	const { createElement, Component } = element;
	const { InspectorControls, FontSizePicker, PanelColorSettings } = blockEditor;
	const { PanelBody, ToggleControl, SelectControl, ColorPicker, RangeControl, TextControl } = components;
	const { __ } = i18n;
	const { useSelect } = data;

	class GoogleReviewsEdit extends Component {
		constructor(props) {
			super(props);
			this.state = {
				reviews: null,
				loading: true,
				error: null
			}
		}

		formatReviewTime(timestamp) {
			const reviewDate = new Date(timestamp * 1000);
			const now = new Date();
			const daysAgo = Math.floor((now - reviewDate) / (24 * 60 * 60 * 1000));

			if (daysAgo < 7) {
				if (daysAgo === 0) {
					return 'Today';
				} else if (daysAgo === 1) {
					return 'Yesterday';
				} else {
					return daysAgo + ' days ago';
				}
			} else {
				return reviewDate.toLocaleDateString('en-US', {
					year: 'numeric',
					month: 'long',
					day: 'numeric'
				});
			}
		};

		componentDidMount() {
			const { attributes } = this.props;
			const { reviewsSort } = attributes;
			this.fetchReviews(reviewsSort);
		}

		componentDidUpdate(prevProps) {
			const { attributes } = this.props;
			const { reviewsSort } = attributes;
			if (prevProps.attributes.reviewsSort !== reviewsSort) {
				this.setState({ loading: true });
				this.fetchReviews(reviewsSort);
			}
		}

		fetchReviews(sortBy = null) {
			const formData = new FormData();
			formData.append('action', 'get_google_reviews');
			formData.append('nonce', googleReviewsAjax.nonce);
			if (sortBy) {
				formData.append('sort_by', sortBy);
			}

			fetch(googleReviewsAjax.ajax_url, {
				method: 'POST',
				body: formData
			})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						this.setState({
							reviews: data.data,
							loading: false
						});
					}
					else {
						this.setState({
							error: data.data || 'Failed to load reviews',
							loading: false
						});
					}
				})
				.catch(error => {
					this.setState({
						error: 'Network error',
						loading: false
					});
				});
		}

		renderStars(rating, starColor, fontSize) {
			const stars = [];
			const filledColor = starColor || '#ffc107';
			const emptyColor = '#ddd';
			
			for (let i = 1; i <= 5; i++) {
				let starClass = 'star empty';
				let starStyle = { 
					color: emptyColor,
					...(fontSize && { fontSize: fontSize })
				};
				
				if (i <= rating) {
					starClass = 'star filled';
					starStyle = { 
						color: filledColor,
						...(fontSize && { fontSize: fontSize })
					};
				}
				else if (i - 0.5 <= rating) {
					starClass = 'star half';
					starStyle = {
						background: `linear-gradient(90deg, ${filledColor} 50%, ${emptyColor} 50%)`,
						WebkitBackgroundClip: 'text',
						WebkitTextFillColor: 'transparent',
						backgroundClip: 'text',
						...(fontSize && { fontSize: fontSize })
					};
				}

				stars.push(
					createElement('span', {
						key: i,
						className: starClass,
						style: starStyle
					}, '★')
				);
			}
			return stars;
		}

		render() {
			const { attributes, setAttributes } = this.props;
			const { 
				showLink, showReviewContent, maxReviews, alignment, backgroundColor, textColor, reviewsSort, 
				overrideUrl, overrideTitle, customRatingText, showTitle, showRatingText, ratingTextBelow,
				titleFontSize, titleFontSizeCustom, ratingTextFontSize, ratingTextFontSizeCustom, 
				reviewsFontSize, reviewsFontSizeCustom, reviewTimeFontSize, reviewTimeFontSizeCustom,
				starsFontSize, starsFontSizeCustom,
				titleTextColor, titleBackgroundColor, ratingTextColor, ratingBackgroundColor, 
				reviewsTextColor, reviewsBackgroundColor, reviewTimeTextColor, reviewTimeBackgroundColor,
				starsTextColor, starsBackgroundColor
			} = attributes;
			const { reviews, loading, error } = this.state;
			
			// Get theme font sizes - use proper theme.json values
			const fontSizes = wp.data.select('core/editor') && wp.data.select('core/editor').getEditorSettings().fontSizes 
				|| wp.data.select('core/block-editor') && wp.data.select('core/block-editor').getSettings().fontSizes 
				|| [
					{ name: __('Small'), size: '0.9rem', slug: 'small' },
					{ name: __('Medium'), size: '1.05rem', slug: 'medium' },
					{ name: __('Large'), size: '1.85rem', slug: 'large' },
					{ name: __('Extra Large'), size: '2.5rem', slug: 'x-large' },
					{ name: __('Extra Extra Large'), size: '3.27rem', slug: 'xx-large' },
				];
			
			// Helper function to get typography styles
			const getTypographyStyle = (fontSize, fontSizeCustom) => {
				if (fontSizeCustom) {
					return { fontSize: fontSizeCustom + 'px' };
				} else if (fontSize) {
					// Find the actual size from fontSizes array
					const fontSizeObj = fontSizes.find(fs => fs.slug === fontSize);
					return fontSizeObj ? { fontSize: fontSizeObj.size } : {};
				}
				return {};
			};
			
			// Helper function to get typography class
			const getTypographyClass = (fontSize) => {
				return fontSize ? 'has-' + fontSize + '-font-size' : '';
			};
			
			// Title styles with individual colors
			const titleStyle = {
				...getTypographyStyle(titleFontSize, titleFontSizeCustom),
				...(titleTextColor && { color: titleTextColor }),
				...(titleBackgroundColor && { backgroundColor: titleBackgroundColor })
			};
			const titleClass = getTypographyClass(titleFontSize);
			
			// Rating text styles with individual colors
			const ratingTextStyle = {
				...getTypographyStyle(ratingTextFontSize, ratingTextFontSizeCustom),
				...(ratingTextColor && { color: ratingTextColor })
			};
			const ratingTextClass = getTypographyClass(ratingTextFontSize);
			
			// Rating section style (for background color)
			const ratingSectionStyle = {
				...(ratingBackgroundColor && { backgroundColor: ratingBackgroundColor })
			};
			
			// Reviews styles with individual colors
			const reviewsStyle = {
				...getTypographyStyle(reviewsFontSize, reviewsFontSizeCustom),
				...(reviewsTextColor && { color: reviewsTextColor }),
				...(reviewsBackgroundColor && { backgroundColor: reviewsBackgroundColor })
			};
			const reviewsClass = getTypographyClass(reviewsFontSize);
			
			// Review time styles with individual colors
			const reviewTimeStyle = {
				...getTypographyStyle(reviewTimeFontSize, reviewTimeFontSizeCustom),
				...(reviewTimeTextColor && { color: reviewTimeTextColor }),
				...(reviewTimeBackgroundColor && { backgroundColor: reviewTimeBackgroundColor })
			};
			const reviewTimeClass = getTypographyClass(reviewTimeFontSize);
			
			// Stars styles with individual colors
			const starsStyle = {
				...getTypographyStyle(starsFontSize, starsFontSizeCustom),
				...(starsTextColor && { color: starsTextColor }),
				...(starsBackgroundColor && { backgroundColor: starsBackgroundColor })
			};
			const starsClass = getTypographyClass(starsFontSize);
			// Get the actual font size value for stars
			const starsFontSizeValue = starsStyle.fontSize;

			// Settings tab controls
			const inspectorControls = createElement(InspectorControls, {},
				createElement(PanelBody, {
						title: __('Display Settings'),
						initialOpen: true
					},
					createElement(ToggleControl, {
						label: __('Show Title'),
						checked: showTitle !== false,
						onChange: (value) => setAttributes({ showTitle: value })
					}),
					showTitle !== false && createElement(TextControl, {
						label: __('Override Title (optional)'),
						value: overrideTitle || '',
						onChange: (value) => setAttributes({ overrideTitle: value }),
						placeholder: __('Enter custom title to override business name'),
						help: __('Leave empty to use business name from settings')
					}),
					createElement(ToggleControl, {
						label: __('Show Rating Text'),
						checked: showRatingText !== false,
						onChange: (value) => setAttributes({ showRatingText: value })
					}),
					showRatingText !== false && createElement(ToggleControl, {
						label: __('Show Rating Text Below Stars'),
						checked: ratingTextBelow === true,
						onChange: (value) => setAttributes({ ratingTextBelow: value }),
						help: __('Display the rating text on a new line below the stars instead of inline')
					}),
					showRatingText !== false && createElement(TextControl, {
						label: __('Custom Rating Text (optional)'),
						value: customRatingText || '',
						onChange: (value) => setAttributes({ customRatingText: value }),
						placeholder: __('e.g., Based on $review_count customer reviews'),
						help: __('Use $review_count where you want the review count to appear. HTML allowed: <p>, <a>, <br>, <strong>, <em>. Leave empty for default "(X reviews)" format.')
					}),
					createElement(ToggleControl, {
						label: __('Show Google Link'),
						checked: showLink,
						onChange: (value) => setAttributes({ showLink: value })
					}),
					showLink && createElement(TextControl, {
						label: __('Override URL (optional)'),
						value: overrideUrl || '',
						onChange: (value) => setAttributes({ overrideUrl: value }),
						placeholder: __('Enter custom URL to override Google Reviews URL'),
						help: __('Leave empty to use Google-provided URL')
					}),
					createElement(ToggleControl, {
						label: __('Show Individual Reviews'),
						checked: showReviewContent,
						onChange: (value) => setAttributes({ showReviewContent: value })
					}                    ),
					showReviewContent && createElement(RangeControl, {
						label: __('Max Reviews to Show (Google API limit: 5)'),
						value: maxReviews,
						onChange: (value) => setAttributes({ maxReviews: value }),
						min: 1,
						max: 5
					}),
					showReviewContent && createElement(SelectControl, {
						label: __('Sort Reviews By'),
						value: reviewsSort || 'most_relevant',
						options: [
							{ label: 'Most Relevant', value: 'most_relevant' },
							{ label: 'Most Recent', value: 'newest' }
						],
						onChange: (value) => setAttributes({ reviewsSort: value })
					})
				)
			);
			
			// Styles tab controls
			const styleControls = createElement(InspectorControls, { group: 'styles' },
				createElement(PanelBody, {
						title: __('Typography'),
						initialOpen: false
					},
					createElement('h4', { style: { marginBottom: '10px' } }, __('Title Font Size')),
					createElement(FontSizePicker, {
						__next40pxDefaultSize: true,
						fontSizes: fontSizes,
						value: titleFontSizeCustom || (titleFontSize ? fontSizes.find(fs => fs.slug === titleFontSize)?.size : undefined),
						onChange: (newFontSize) => {
							// Handle preset font sizes (string values like '0.9rem')
							const preset = fontSizes.find(fs => fs.size === newFontSize);
							if (preset) {
								// Preset selected - store the slug
								setAttributes({ 
									titleFontSize: preset.slug,
									titleFontSizeCustom: undefined 
								});
							} else if (typeof newFontSize === 'number') {
								// Custom numeric size - store as custom
								setAttributes({ 
									titleFontSize: '',
									titleFontSizeCustom: newFontSize 
								});
							} else if (newFontSize === undefined) {
								// Clear font size
								setAttributes({ 
									titleFontSize: '',
									titleFontSizeCustom: undefined 
								});
							}
						}
					}),
					createElement('h4', { style: { marginTop: '20px', marginBottom: '10px' } }, __('Rating Text Font Size')),
					createElement(FontSizePicker, {
						__next40pxDefaultSize: true,
						fontSizes: fontSizes,
						value: ratingTextFontSizeCustom || (ratingTextFontSize ? fontSizes.find(fs => fs.slug === ratingTextFontSize)?.size : undefined),
						onChange: (newFontSize) => {
							// Handle preset font sizes
							const preset = fontSizes.find(fs => fs.size === newFontSize);
							if (preset) {
								// Preset selected
								setAttributes({ 
									ratingTextFontSize: preset.slug,
									ratingTextFontSizeCustom: undefined 
								});
							} else if (typeof newFontSize === 'number') {
								// Custom numeric size
								setAttributes({ 
									ratingTextFontSize: '',
									ratingTextFontSizeCustom: newFontSize 
								});
							} else if (newFontSize === undefined) {
								// Clear font size
								setAttributes({ 
									ratingTextFontSize: '',
									ratingTextFontSizeCustom: undefined 
								});
							}
						}
					}),
					createElement('h4', { style: { marginTop: '20px', marginBottom: '10px' } }, __('Reviews Font Size')),
					createElement(FontSizePicker, {
						__next40pxDefaultSize: true,
						fontSizes: fontSizes,
						value: reviewsFontSizeCustom || (reviewsFontSize ? fontSizes.find(fs => fs.slug === reviewsFontSize)?.size : undefined),
						onChange: (newFontSize) => {
							// Handle preset font sizes
							const preset = fontSizes.find(fs => fs.size === newFontSize);
							if (preset) {
								// Preset selected
								setAttributes({ 
									reviewsFontSize: preset.slug,
									reviewsFontSizeCustom: undefined 
								});
							} else if (typeof newFontSize === 'number') {
								// Custom numeric size
								setAttributes({ 
									reviewsFontSize: '',
									reviewsFontSizeCustom: newFontSize 
								});
							} else if (newFontSize === undefined) {
								// Clear font size
								setAttributes({ 
									reviewsFontSize: '',
									reviewsFontSizeCustom: undefined 
								});
							}
						}
					}),
					createElement('h4', { style: { marginTop: '20px', marginBottom: '10px' } }, __('Review Time Font Size')),
					createElement(FontSizePicker, {
						__next40pxDefaultSize: true,
						fontSizes: fontSizes,
						value: reviewTimeFontSizeCustom || (reviewTimeFontSize ? fontSizes.find(fs => fs.slug === reviewTimeFontSize)?.size : undefined),
						onChange: (newFontSize) => {
							// Handle preset font sizes
							const preset = fontSizes.find(fs => fs.size === newFontSize);
							if (preset) {
								// Preset selected
								setAttributes({ 
									reviewTimeFontSize: preset.slug,
									reviewTimeFontSizeCustom: undefined 
								});
							} else if (typeof newFontSize === 'number') {
								// Custom numeric size
								setAttributes({ 
									reviewTimeFontSize: '',
									reviewTimeFontSizeCustom: newFontSize 
								});
							} else if (newFontSize === undefined) {
								// Clear font size
								setAttributes({ 
									reviewTimeFontSize: '',
									reviewTimeFontSizeCustom: undefined 
								});
							}
						}
					}),
					createElement('h4', { style: { marginTop: '20px', marginBottom: '10px' } }, __('Stars Font Size')),
					createElement(FontSizePicker, {
						__next40pxDefaultSize: true,
						fontSizes: fontSizes,
						value: starsFontSizeCustom || (starsFontSize ? fontSizes.find(fs => fs.slug === starsFontSize)?.size : undefined),
						onChange: (newFontSize) => {
							// Handle preset font sizes
							const preset = fontSizes.find(fs => fs.size === newFontSize);
							if (preset) {
								// Preset selected
								setAttributes({ 
									starsFontSize: preset.slug,
									starsFontSizeCustom: undefined 
								});
							} else if (typeof newFontSize === 'number') {
								// Custom numeric size
								setAttributes({ 
									starsFontSize: '',
									starsFontSizeCustom: newFontSize 
								});
							} else if (newFontSize === undefined) {
								// Clear font size
								setAttributes({ 
									starsFontSize: '',
									starsFontSizeCustom: undefined 
								});
							}
						}
					})
				),
				createElement(PanelBody, {
					title: __('Colors'),
					initialOpen: false
				},
					createElement(PanelColorSettings, {
						title: __('Color Settings'),
						initialOpen: false,
						colorSettings: [
							{
								value: backgroundColor,
								onChange: (value) => setAttributes({ backgroundColor: value }),
								label: __('Block Background Color')
							},
							{
								value: textColor,
								onChange: (value) => setAttributes({ textColor: value }),
								label: __('Default Text Color')
							}
						]
					}),
					createElement(PanelColorSettings, {
						title: __('Title Colors'),
						initialOpen: false,
						colorSettings: [
							{
								value: titleTextColor,
								onChange: (value) => setAttributes({ titleTextColor: value }),
								label: __('Title Text Color')
							},
							{
								value: titleBackgroundColor,
								onChange: (value) => setAttributes({ titleBackgroundColor: value }),
								label: __('Title Background Color')
							}
						]
					}),
					createElement(PanelColorSettings, {
						title: __('Rating Colors'),
						initialOpen: false,
						colorSettings: [
							{
								value: ratingTextColor,
								onChange: (value) => setAttributes({ ratingTextColor: value }),
								label: __('Rating Text Color')
							},
							{
								value: ratingBackgroundColor,
								onChange: (value) => setAttributes({ ratingBackgroundColor: value }),
								label: __('Rating Background Color')
							}
						]
					}),
					createElement(PanelColorSettings, {
						title: __('Reviews Colors'),
						initialOpen: false,
						colorSettings: [
							{
								value: reviewsTextColor,
								onChange: (value) => setAttributes({ reviewsTextColor: value }),
								label: __('Reviews Text Color')
							},
							{
								value: reviewsBackgroundColor,
								onChange: (value) => setAttributes({ reviewsBackgroundColor: value }),
								label: __('Reviews Background Color')
							}
						]
					}),
					createElement(PanelColorSettings, {
						title: __('Review Time Colors'),
						initialOpen: false,
						colorSettings: [
							{
								value: reviewTimeTextColor,
								onChange: (value) => setAttributes({ reviewTimeTextColor: value }),
								label: __('Review Time Text Color')
							},
							{
								value: reviewTimeBackgroundColor,
								onChange: (value) => setAttributes({ reviewTimeBackgroundColor: value }),
								label: __('Review Time Background Color')
							}
						]
					}),
					createElement(PanelColorSettings, {
						title: __('Stars Colors'),
						initialOpen: false,
						colorSettings: [
							{
								value: starsTextColor,
								onChange: (value) => setAttributes({ starsTextColor: value }),
								label: __('Stars Color')
							},
							{
								value: starsBackgroundColor,
								onChange: (value) => setAttributes({ starsBackgroundColor: value }),
								label: __('Stars Background Color')
							}
						]
					})
				)
			);

			if (loading) {
				return [
					inspectorControls,
					styleControls,
					createElement('div', {
						className: 'google-reviews-block loading',
						style: {
							backgroundColor: backgroundColor,
							color: textColor
						}
					}, __('Loading Google Reviews...'))
				];
			}

			if (error) {
				return [
					inspectorControls,
					styleControls,
					createElement('div', {
							className: 'google-reviews-block error',
							style: {
								backgroundColor: backgroundColor,
								color: textColor,
								textAlign: alignment
							}
						},
						createElement('p', {}, __('Error: ') + error),
						createElement('p', {}, __('Please check your settings in Settings → Google Reviews'))
					)
				];
			}


			return [
				inspectorControls,
				styleControls,
				createElement('div', {
						className: 'google-reviews-block',
						style: {
							backgroundColor: backgroundColor,
							color: textColor
						}
					},
					showTitle !== false ? createElement('div', { className: 'review-header' },
						createElement('h3', {
							className: titleClass,
							style: titleStyle,
							dangerouslySetInnerHTML: { 
								__html: this.sanitizeHtml(overrideTitle || reviews.name)
							}
						})
					) : null,
					createElement('div', { 
						className: 'review-rating',
						style: ratingSectionStyle
					},
						createElement('span', { className: 'rating-number' }, reviews.rating.toFixed(1)),
						createElement('span', { 
							className: 'stars ' + starsClass,
							style: starsStyle 
						}, this.renderStars(reviews.rating, starsTextColor, starsFontSizeValue)),
						(showRatingText !== false && !ratingTextBelow) ? (
							customRatingText 
								? createElement('span', { 
									className: 'rating-count ' + ratingTextClass,
									style: ratingTextStyle,
									dangerouslySetInnerHTML: { 
										__html: this.sanitizeHtml(customRatingText.replace('$review_count', reviews.total_ratings))
									}
								})
								: createElement('span', { 
									className: 'rating-count ' + ratingTextClass,
									style: ratingTextStyle
								}, '(' + reviews.total_ratings + ' reviews)')
						) : null
					),
					(showRatingText !== false && ratingTextBelow) ? (
						customRatingText 
							? createElement('div', { 
								className: 'rating-count-below ' + ratingTextClass,
								style: ratingTextStyle,
								dangerouslySetInnerHTML: { 
									__html: this.sanitizeHtml(customRatingText.replace('$review_count', reviews.total_ratings))
								}
							})
							: createElement('div', { 
								className: 'rating-count-below ' + ratingTextClass,
								style: ratingTextStyle
							}, '(' + reviews.total_ratings + ' reviews)')
					) : null,
					showReviewContent && reviews && reviews.reviews && reviews.reviews.length > 0 ?
						createElement('div', { className: 'reviews-container' },
							createElement('div', { className: 'reviews-sort-info' },
								createElement('small', {}, 'Sorted by: ' + (reviewsSort === 'newest' ? 'Most Recent' : 'Most Relevant'))
							),
							createElement('div', { 
								className: 'individual-reviews ' + reviewsClass,
								style: reviewsStyle
							},
						reviews.reviews.slice(0, maxReviews).map((review, index) =>
							createElement('div', {
									key: index,
									className: 'review-item'
								},
								createElement('div', { className: 'review-header' },
									createElement('div', { className: 'reviewer-info' },
										review.profile_photo_url ? createElement('img', {
											src: review.profile_photo_url,
											alt: review.author_name,
											className: 'reviewer-photo'
										}) : null,
										createElement('div', { className: 'reviewer-details' },
											createElement('span', { 
												className: 'reviewer-name',
												style: reviewsStyle
											}, review.author_name),
											createElement('div', { className: 'review-rating-individual' },
												createElement('div', { 
													className: 'stars ' + starsClass,
													style: starsStyle
												},
													this.renderStars(review.rating, starsTextColor, starsFontSizeValue)
												),
												createElement('span', { 
													className: 'review-time ' + reviewTimeClass,
													style: reviewTimeStyle
												},
													this.formatReviewTime(review.time)
												)
											)
										)
									)
								),
								review.text ? createElement('div', { 
									className: 'review-text',
									style: reviewsStyle,
									dangerouslySetInnerHTML: { __html: review.text.replace(/\n/g, '<br />') }
								}) : null
							)
						)
					)
					) : null,
					showLink && (overrideUrl || (reviews && reviews.url)) ? createElement('div', { className: 'review-link' },
						createElement('a', {
							href: overrideUrl || reviews.url,
							target: '_blank',
							rel: 'noopener'
						}, __('See All Reviews on Google'))
					) : null
				)
			];
		}

		sanitizeHtml(html) {
			// Basic HTML sanitization for allowed tags
			// This is a simple implementation - the server-side wp_kses provides the real security
			const allowedTags = ['p', 'a', 'br', 'strong', 'em'];
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = html;
			
			// Remove script tags and other dangerous elements
			const scripts = tempDiv.querySelectorAll('script, style, iframe, object, embed');
			scripts.forEach(el => el.remove());
			
			// Remove event handlers
			const allElements = tempDiv.querySelectorAll('*');
			allElements.forEach(el => {
				for (let attr of el.attributes) {
					if (attr.name.startsWith('on')) {
						el.removeAttribute(attr.name);
					}
				}
			});
			
			return tempDiv.innerHTML;
		}


		formatReviewTime(timestamp) {
			const reviewDate = new Date(timestamp * 1000);
			const now = new Date();
			const daysAgo = Math.floor((now - reviewDate) / (24 * 60 * 60 * 1000));

			if (daysAgo < 7) {
				if (daysAgo === 0) {
					return 'Today';
				} else if (daysAgo === 1) {
					return 'Yesterday';
				} else {
					return daysAgo + ' days ago';
				}
			} else {
				return reviewDate.toLocaleDateString('en-US', {
					year: 'numeric',
					month: 'long',
					day: 'numeric'
				});
			}
		}
	}

	registerBlockType('eightyfourem/google-reviews', {
		edit: GoogleReviewsEdit,
		save: function() {
			// Server-side rendering
			return null;
		}
	});

})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor || window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.data
);
