<?php
/**
 * Plugin Name: Breakfast Status Widget
 * Version: 0.1
 * Author: Bryan Garcia

 */

require( __DIR__ . '/Minecraft-Query/MinecraftQuery.class.php' );


$breakfast_status_icon = array( 'bacon', 'bagel', 'donut' );


class Breakfast_Status_Widget extends WP_Widget {
	public function __construct() {
		parent::WP_Widget(
			'breakfast_status_widget',
			__( 'Breakfast Status Widget', 'text_domain' ),
			array( 'description' => __( 'A Minecraft server status widget', 'text_domain' ), )
		);
	}

	public function widget( $args, $instance ) {
		$Query   = new MinecraftQuery();
		$players = get_transient( $args['widget_id'] . '_players' );
		$status  = get_transient( $args['widget_id'] . '_status' );

		if ( $status === false ) {
			try {
				$Query->Connect( $instance['query_host'], $instance['query_port'] );
				$players = $Query->GetPlayers();
				$info    = $Query->GetInfo();
				set_transient( $args['widget_id'] . '_players', $players, 60 );
				if ( is_array( $info ) ) {
					set_transient( $args['widget_id'] . '_status', 'up', 60 );
					$status = 'up';
				} else {
					set_transient( $args['widget_id'] . '_status', 'down', 60 );
					$status = 'down';
				}
			} catch ( MinecraftQueryException $e ) {
				$status = 'down';
			}
		}

		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<div class="row">


			<div class="col-xs-3">
				<img
					src="<?php echo plugins_url(); ?>/breakfast-status/icons/<?php echo $instance['icon']; ?>_<?php echo $status; ?>.png"/>
			</div>
			<div class="col-xs-9">
				<p><?php echo $instance['mod_pack']; ?></p>

				<p><?php echo $instance['server_address']; ?></p>
			</div>
		</div>
		<div class="row" style="margin-top: 5px;">
			<div class="col-xs-12">
				<?php if ( ! empty( $players ) ) : ?>

					<?php foreach ( $players as $player ): ?>
						<a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php echo $player ?>"><img
								src="<?php echo plugins_url(); ?>/breakfast-status/Minecraft-Avatar/face.php?s=24&u=<?php echo $player; ?>"></a>
					<?php endforeach; ?>

				<?php endif; ?>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		global $breakfast_status_icon;
		$defaults = array(
			'title'          => 'Server',
			'query_host'     => 'localhost',
			'query_port'     => '25576',
			'mod_pack'       => 'Vanilla',
			'server_address' => 'localhost',
			'icon'           => 'bacon',
		);

		$instance = wp_parse_args( $instance, $defaults );

		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>"
			       type="text" value="<?php echo esc_attr( $instance['title'] ); ?>"/>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'mod_pack' ); ?>"><?php _e( 'Mod Pack:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'mod_pack' ); ?>"
			       name="<?php echo $this->get_field_name( 'mod_pack' ); ?>"
			       type="text" value="<?php echo esc_attr( $instance['mod_pack'] ); ?>"/>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'server_address' ); ?>"><?php _e( 'Server Address:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'server_address' ); ?>"
			       name="<?php echo $this->get_field_name( 'server_address' ); ?>"
			       type="text" value="<?php echo esc_attr( $instance['server_address'] ); ?>"/>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'query_host' ); ?>"><?php _e( 'Query Host:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( ' query_host' ); ?>"
			       name="<?php echo $this->get_field_name( 'query_host' ); ?>"
			       type="text" value="<?php echo esc_attr( $instance['query_host'] ); ?>"/>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'query_port' ); ?>"><?php _e( 'Query Port:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'query_port' ); ?>"
			       name="<?php echo $this->get_field_name( 'query_port' ); ?>"
			       type="text" value="<?php echo esc_attr( $instance['query_port'] ); ?>"/>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'icon' ); ?>"><?php _e( 'Icon:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'icon' ); ?>"
			        name="<?php echo $this->get_field_name( 'icon' ); ?>">
				<?php foreach ( $breakfast_status_icon as $value ) : ?>
					<option
						value="<?php echo $value; ?>" <?php selected( $instance['icon'], $value, false ); ?>><?php echo $value; ?> </option>
				<?php endforeach; ?>
			</select>
		</p>

	<?php
	}
}

function register_breakfast_status_widget() {
	register_widget( 'Breakfast_Status_Widget' );
}

add_action( 'widgets_init', 'register_breakfast_status_widget' );