<?php namespace JasonLewis\ResourceWatcher;

use JasonLewis\ResourceWatcher\Resource\ResourceInterface;

class Tracker {

	/**
	 * Array of tracked resources.
	 *
	 * @var array
	 */
	protected $tracked = array();

	/**
	 * Register a resource with the tracker.
	 *
	 * @param  JasonLewis\ResourceWatcher\Resource\ResourceInterface  $resource
	 * @param  JasonLewis\ResourceWatcher\Listener  $listener
	 * @return void
	 */
	public function register(ResourceInterface $resource, Listener $listener)
	{
		$this->tracked[$resource->getKey()] = array($resource, $listener);
	}

	/**
	 * Determine if a resource is tracked.
	 *
	 * @param  JasonLewis\ResourceWatcher\Resource\Resource  $resource
	 */
	public function isTracked(Resource $resource)
	{
		return isset($this->tracked[$resource->getKey()]);
	}

	/**
	 * Get the tracked resources.
	 *
	 * @return array
	 */
	public function getTracked()
	{
		return $this->tracked;
	}

	/**
	 * Detect any changes on the tracked resources.
	 *
	 * @return void
	 */
	public function checkTrackings()
	{
		foreach ($this->tracked as $name => $tracked)
		{
			list($resource, $listener) = $tracked;

			if ( ! $events = $resource->detectChanges())
			{
				continue;
			}

			foreach ($events as $event)
			{
				if ($event instanceof Event)
				{
					$this->callListenerBindings($listener, $event);
				}
			}
		}
	}

	/**
	 * Call the bindings on the listener for a given event.
	 *
	 * @param  JasonLewis\ResourceWatcher\Listener  $listener
	 * @param  JasonLewis\ResourceWatcher\Event  $event
	 * @return void
	 */
	protected function callListenerBindings(Listener $listener, Event $event)
	{
		$binding = $listener->determineEventBinding($event);

		if ($listener->hasBinding($binding))
		{
			foreach ($listener->getBindings($binding) as $callback)
			{
				$resource = $event->getResource();

				call_user_func($callback, $resource, $resource->getPath());
			}
		}
	}

}