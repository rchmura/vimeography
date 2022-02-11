import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import { SelectControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

registerBlockType("vimeography/gallery", {
	title: __("Vimeography Gallery", "vimeography"),
	description: __(
		"Display your Vimeo videos in a beautiful gallery layout.",
		"vimeography"
	),
	keywords: [__("gallery"), __("video")],
	category: "embed",
	icon: "slides",

	// Necessary for saving block content.
	// Note: you should eventually add all possible shortcode atts here.
	attributes: {
		id: {
			type: "string"
		}
	},

	/**
	 * Optional block extended support features.
	 */
	supports: {
		// Removes support for an HTML mode.
		html: false
	},

	edit({ className, attributes, setAttributes, isSelected }) {
		const galleries = window.vimeography_galleries.map(({ id, title }) => ({
			value: id,
			label: title
		}));

		const options = [
			{ value: null, label: "Select a Gallery", disabled: false },
			...galleries
		];

		return (
			<>
				{isSelected && (
					<InspectorControls key="inspector">
						<p>
							{__(
								"Choose the gallery to display in the main block editor.",
								"vimeography"
							)}
						</p>
					</InspectorControls>
				)}
				<div className={className}>
					<label> {__("Select a Video Gallery", "vimeography")}</label>
					<SelectControl
						value={attributes.id}
						options={options}
						onChange={id => setAttributes({ id })}
					/>
				</div>
			</>
		);
	},

	save({ attributes, className }) {
		return (
			<div className={className}>
				<div className="vimeography-gallery-block">
					<p>{attributes.id}</p>
				</div>
			</div>
		);
	}
});
