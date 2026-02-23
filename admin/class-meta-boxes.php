<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Meta_Boxes {

	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_pcip_question', array( $this, 'save_question_meta' ) );
		add_action( 'save_post_pcip_issue_report', array( $this, 'save_report_meta' ) );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'pcip_question_details',
			__( 'Question Details', 'pcip-prep' ),
			array( $this, 'render_question_meta_box' ),
			'pcip_question',
			'normal',
			'high'
		);

		add_meta_box(
			'pcip_issue_report_details',
			__( 'Report Details', 'pcip-prep' ),
			array( $this, 'render_report_meta_box' ),
			'pcip_issue_report',
			'normal',
			'high'
		);
	}

	/**
	 * Render the question editor meta box.
	 */
	public function render_question_meta_box( $post ) {
		wp_nonce_field( 'pcip_question_meta', 'pcip_question_meta_nonce' );

		$type          = get_post_meta( $post->ID, '_pcip_question_type', true ) ?: 'multiple_choice';
		$question_text = get_post_meta( $post->ID, '_pcip_question_text', true );
		$answer        = get_post_meta( $post->ID, '_pcip_answer', true );
		$option_a      = get_post_meta( $post->ID, '_pcip_option_a', true );
		$option_b      = get_post_meta( $post->ID, '_pcip_option_b', true );
		$option_c      = get_post_meta( $post->ID, '_pcip_option_c', true );
		$option_d      = get_post_meta( $post->ID, '_pcip_option_d', true );
		$correct       = get_post_meta( $post->ID, '_pcip_correct_answer', true );
		$explanation   = get_post_meta( $post->ID, '_pcip_explanation', true );
		$difficulty    = get_post_meta( $post->ID, '_pcip_difficulty', true ) ?: 'medium';
		$reference     = get_post_meta( $post->ID, '_pcip_reference', true );
		?>
		<style>
			.pcip-meta-field { margin-bottom: 16px; }
			.pcip-meta-field label { display: block; font-weight: 600; margin-bottom: 4px; }
			.pcip-meta-field textarea,
			.pcip-meta-field input[type="text"] { width: 100%; }
			.pcip-meta-field textarea { min-height: 80px; }
			.pcip-mc-fields, .pcip-fc-fields { display: none; }
			.pcip-mc-fields.active, .pcip-fc-fields.active { display: block; }
			.pcip-options-group { background: #f9f9f9; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
			.pcip-options-group .pcip-meta-field { margin-bottom: 10px; }
		</style>

		<div class="pcip-meta-field">
			<label for="pcip_question_type"><?php esc_html_e( 'Question Type', 'pcip-prep' ); ?></label>
			<select id="pcip_question_type" name="_pcip_question_type">
				<option value="multiple_choice" <?php selected( $type, 'multiple_choice' ); ?>>
					<?php esc_html_e( 'Multiple Choice', 'pcip-prep' ); ?>
				</option>
				<option value="flashcard" <?php selected( $type, 'flashcard' ); ?>>
					<?php esc_html_e( 'Flashcard', 'pcip-prep' ); ?>
				</option>
			</select>
		</div>

		<div class="pcip-meta-field">
			<label for="pcip_question_text"><?php esc_html_e( 'Question Text', 'pcip-prep' ); ?></label>
			<textarea id="pcip_question_text" name="_pcip_question_text" rows="4"><?php echo esc_textarea( $question_text ); ?></textarea>
		</div>

		<!-- Flashcard fields -->
		<div class="pcip-fc-fields <?php echo 'flashcard' === $type ? 'active' : ''; ?>">
			<div class="pcip-meta-field">
				<label for="pcip_answer"><?php esc_html_e( 'Answer', 'pcip-prep' ); ?></label>
				<textarea id="pcip_answer" name="_pcip_answer" rows="4"><?php echo esc_textarea( $answer ); ?></textarea>
			</div>
		</div>

		<!-- Multiple choice fields -->
		<div class="pcip-mc-fields <?php echo 'multiple_choice' === $type ? 'active' : ''; ?>">
			<div class="pcip-options-group">
				<h4 style="margin-top:0"><?php esc_html_e( 'Answer Options', 'pcip-prep' ); ?></h4>
				<?php foreach ( array( 'a', 'b', 'c', 'd' ) as $letter ) :
					$var = 'option_' . $letter;
					?>
					<div class="pcip-meta-field">
						<label for="pcip_option_<?php echo esc_attr( $letter ); ?>">
							<?php echo esc_html( 'Option ' . strtoupper( $letter ) ); ?>
						</label>
						<textarea id="pcip_option_<?php echo esc_attr( $letter ); ?>"
							name="_pcip_option_<?php echo esc_attr( $letter ); ?>"
							rows="2"><?php echo esc_textarea( $$var ); ?></textarea>
					</div>
				<?php endforeach; ?>

				<div class="pcip-meta-field">
					<label for="pcip_correct_answer"><?php esc_html_e( 'Correct Answer', 'pcip-prep' ); ?></label>
					<select id="pcip_correct_answer" name="_pcip_correct_answer_letter">
						<?php foreach ( array( 'a', 'b', 'c', 'd' ) as $letter ) :
							$option_text = ${'option_' . $letter};
							$is_correct  = ( $correct === $option_text && '' !== $option_text );
							?>
							<option value="<?php echo esc_attr( $letter ); ?>" <?php selected( $is_correct ); ?>>
								<?php echo esc_html( strtoupper( $letter ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="pcip-meta-field" style="margin-top:16px">
				<label for="pcip_explanation"><?php esc_html_e( 'Explanation', 'pcip-prep' ); ?></label>
				<textarea id="pcip_explanation" name="_pcip_explanation" rows="4"><?php echo esc_textarea( $explanation ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Displayed after the user answers. Explain why the correct answer is right.', 'pcip-prep' ); ?></p>
			</div>
		</div>

		<div class="pcip-meta-field">
			<label for="pcip_difficulty"><?php esc_html_e( 'Difficulty', 'pcip-prep' ); ?></label>
			<select id="pcip_difficulty" name="_pcip_difficulty">
				<option value="easy" <?php selected( $difficulty, 'easy' ); ?>><?php esc_html_e( 'Easy', 'pcip-prep' ); ?></option>
				<option value="medium" <?php selected( $difficulty, 'medium' ); ?>><?php esc_html_e( 'Medium', 'pcip-prep' ); ?></option>
				<option value="hard" <?php selected( $difficulty, 'hard' ); ?>><?php esc_html_e( 'Hard', 'pcip-prep' ); ?></option>
			</select>
		</div>

		<div class="pcip-meta-field">
			<label for="pcip_reference"><?php esc_html_e( 'PCI Reference', 'pcip-prep' ); ?></label>
			<input type="text" id="pcip_reference" name="_pcip_reference"
				value="<?php echo esc_attr( $reference ); ?>"
				placeholder="e.g., Req 3.4.1, PCI DSS v4.0.1 Section 6" />
		</div>

		<script>
		(function(){
			var typeSelect = document.getElementById('pcip_question_type');
			var mcFields   = document.querySelector('.pcip-mc-fields');
			var fcFields   = document.querySelector('.pcip-fc-fields');
			typeSelect.addEventListener('change', function(){
				mcFields.classList.toggle('active', this.value === 'multiple_choice');
				fcFields.classList.toggle('active', this.value === 'flashcard');
			});
		})();
		</script>
		<?php
	}

	/**
	 * Save question meta on post save.
	 */
	public function save_question_meta( $post_id ) {
		if ( ! isset( $_POST['pcip_question_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['pcip_question_meta_nonce'], 'pcip_question_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$type = sanitize_text_field( $_POST['_pcip_question_type'] ?? 'multiple_choice' );
		update_post_meta( $post_id, '_pcip_question_type', $type );
		update_post_meta( $post_id, '_pcip_question_text', sanitize_textarea_field( $_POST['_pcip_question_text'] ?? '' ) );

		if ( 'flashcard' === $type ) {
			update_post_meta( $post_id, '_pcip_answer', sanitize_textarea_field( $_POST['_pcip_answer'] ?? '' ) );
		} else {
			$options = array();
			foreach ( array( 'a', 'b', 'c', 'd' ) as $letter ) {
				$key  = '_pcip_option_' . $letter;
				$text = sanitize_textarea_field( $_POST[ $key ] ?? '' );
				update_post_meta( $post_id, $key, $text );
				$options[ $letter ] = $text;
			}

			// Store correct answer as text.
			$correct_letter = sanitize_text_field( $_POST['_pcip_correct_answer_letter'] ?? 'a' );
			$correct_text   = $options[ $correct_letter ] ?? '';
			update_post_meta( $post_id, '_pcip_correct_answer', $correct_text );

			update_post_meta( $post_id, '_pcip_explanation', sanitize_textarea_field( $_POST['_pcip_explanation'] ?? '' ) );
		}

		update_post_meta( $post_id, '_pcip_difficulty', sanitize_text_field( $_POST['_pcip_difficulty'] ?? 'medium' ) );
		update_post_meta( $post_id, '_pcip_reference', sanitize_text_field( $_POST['_pcip_reference'] ?? '' ) );
	}

	/**
	 * Render the issue report meta box.
	 */
	public function render_report_meta_box( $post ) {
		include PCIP_PREP_PLUGIN_DIR . 'admin/views/issue-report-meta.php';
	}

	/**
	 * Save issue report meta on post save.
	 */
	public function save_report_meta( $post_id ) {
		if ( ! isset( $_POST['pcip_report_meta_nonce'] )
			|| ! wp_verify_nonce( $_POST['pcip_report_meta_nonce'], 'pcip_report_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$status = sanitize_text_field( $_POST['_pcip_report_status'] ?? 'open' );
		update_post_meta( $post_id, '_pcip_report_status', $status );
	}
}
